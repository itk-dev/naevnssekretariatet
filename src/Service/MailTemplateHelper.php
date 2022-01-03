<?php

namespace App\Service;

use App\Entity\MailTemplate;
use App\Repository\MailTemplateMacroRepository;
use App\Repository\MailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

class MailTemplateHelper
{
    /**
     * The options.
     *
     * @var array
     */
    private $options;

    public function __construct(private MailTemplateRepository $mailTemplateRepository, private MailTemplateMacroRepository $mailTemplateMacroRepository, private EntityManagerInterface $entityManager, private SerializerInterface $serializer, private Filesystem $filesystem, array $mailTemplateHelperOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($mailTemplateHelperOptions);
    }

    public function getMailTemplateTypeChoices(): array
    {
        return array_flip(array_map(static fn (array $spec) => $spec['label'], $this->options['template_types']));
    }

    /**
     * Get entity for previewing a mail template.
     *
     * @return mixed The entity
     *
     * @throws \RuntimeException
     */
    public function getPreviewEntity(MailTemplate $mailTemplate)
    {
        $spec = $this->options['template_types'][$mailTemplate->getType()] ?? null;
        if (isset($spec['entity_class_names']) && is_array($spec['entity_class_names'])) {
            foreach ($spec['entity_class_names'] as $className) {
                try {
                    $repository = $this->entityManager->getRepository($className);
                    $entity = $repository->findOneBy([]);
                    if (null !== $entity) {
                        return $entity;
                    }
                } catch (\Exception $exception) {
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Cannot get preview entity for mail template %s of type %s', $mailTemplate->getName(), $mailTemplate->getType()));
    }

    public function getTemplateData(MailTemplate $mailTemplate, $entity): array
    {
        $templateFileName = $this->getTemplateFile($mailTemplate);
        $templateProcessor = new TemplateProcessor($templateFileName);

        return $this->getValues($entity, $templateProcessor);
    }

    /**
     * Render mail template to a file.
     *
     * @param $entity
     */
    public function renderMailTemplate(MailTemplate $mailTemplate, $entity = null): string
    {
        $templateFileName = $this->getTemplateFile($mailTemplate);
        // https://phpword.readthedocs.io/en/latest/templates-processing.html
        $templateProcessor = new TemplateProcessor($templateFileName);

        // Handle macros.
        $templateType = $mailTemplate->getType();
        $macros = $this->mailTemplateMacroRepository->findByTemplateType($templateType);
        foreach ($macros as $macro) {
            $lines = explode(PHP_EOL, trim($macro->getContent()));
            $element = new TextRun();
            foreach ($lines as $index => $line) {
                if ($index > 0) {
                    $element->addTextBreak();
                }
                $element->addText($line);
            }

            $this->setTemplateValue($macro->getMacro(), $element, $templateProcessor);
        }
        $values = $this->getValues($entity, $templateProcessor);
        $this->setTemplateValues($values, $templateProcessor);
        $processedFileName = $templateProcessor->save();

        $client = HttpClient::create($this->options['colabora_http_client_options']);
        $formFields = [
            'data' => DataPart::fromPath($processedFileName),
        ];
        $formData = new FormDataPart($formFields);

        // curl --insecure --form "data=@mail_template_001.docx" https://libreoffice:9980/lool/convert-to/pdf
        // https://sdk.collaboraonline.com/docs/conversion_api.html?highlight=convert
        try {
            $response = $client->request('POST', '/lool/convert-to/pdf', [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);
            $content = $response->getContent();
        } catch (ClientException $exception) {
            // @todo How to handle this?
        }

        $fileName = $this->filesystem->tempnam('/tmp/', 'mail_template', '.pdf');
        file_put_contents($fileName, $content);

        return $fileName;
    }

    public function getTemplateFile(MailTemplate $mailTemplate): string
    {
        return rtrim($this->options['template_file_directory'] ?? '', '/').'/'.$mailTemplate->getTemplateFilename();
    }

    public function getTemplates(string $type): array
    {
        return $this->mailTemplateRepository->findBy(['type' => $type]);
    }

    /**
     * Get values from an entity.
     *
     * @param $entity
     *
     * @return array|false|mixed|string[]
     */
    private function getValues(?object $entity, TemplateProcessor $templateProcessor)
    {
        $placeHolders = $templateProcessor->getVariables();

        $values = [];

        if (null === $entity) {
            // We don't have any data; highlight placeholders with colored markers.
            $values = array_combine(
                $placeHolders,
                array_map(static function ($name) {
                    $marker = new TextRun();
                    $marker->addText('${'.$name.'}', ['bgColor' => 'FFFF99']);

                    return $marker;
                }, $placeHolders)
            );
        } else {
            // Serialize to csv to flatten array values and get keys separated by `.`, i.e.
            //   ['name' => ['given' => 'Anders', 'family' => 'And']]
            // will be converted to
            //   ['name.given' => 'Anders', 'name.family' => 'And']
            $csv = $this->serializer->serialize($entity, 'csv', ['groups' => ['mail_template']]);
            // We now have a csv string with two lines (the first is the header) that we split and parse.
            [$header, $row] = array_map('str_getcsv', explode(PHP_EOL, $csv, 2));
            /**
             * @psalm-suppress InvalidArgument
             *
             * Argument 1 of array_combine expects array<array-key, array-key>, non-empty-list<null|string> provided (see https://psalm.dev/004)
             */
            $values = array_combine($header, $row);
        }

        // Add some default values.
        $values['now'] = (new \DateTimeImmutable('now'))->format(\DateTimeImmutable::ATOM);
        $values['today'] = (new \DateTimeImmutable('today'))->format(\DateTimeImmutable::ATOM);

        foreach ($placeHolders as $placeHolder) {
            if (preg_match('/^(?P<key>[^:]+):(?P<format>.+)$/', $placeHolder, $matches)) {
                [, $key, $format] = $matches;
                if (isset($values[$key])) {
                    $values[$placeHolder] = $this->formatValue($values[$key], $format);
                }
            }
        }

        // Set empty string values for all template variables without a value.
        $values += array_map(static function ($count) { return ''; }, $templateProcessor->getVariableCount());

        return $values;
    }

    /**
     * Format a value.
     */
    private function formatValue(string $value, string $format): string
    {
        try {
            $date = new \DateTimeImmutable($value);
            $formatter = new \IntlDateFormatter(\Locale::getDefault(), \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
            // https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
            $formatter->setPattern($format);

            return $formatter->format($date);
        } catch (\Exception $exception) {
        }

        return $value;
    }

    private function setTemplateValue(string $name, $value, TemplateProcessor $templateProcessor)
    {
        if ($value instanceof AbstractElement) {
            $count = 0;
            // TemplateProcessor::setComplexValue() only replaces one (the first) occurrence.
            while (isset($templateProcessor->getVariableCount()[$name]) && $count < 10) {
                $templateProcessor->setComplexValue($name, $value);
                ++$count;
            }
        } else {
            $templateProcessor->setValue($name, $value);
        }
    }

    private function setTemplateValues(array $values, TemplateProcessor $templateProcessor)
    {
        foreach ($values as $name => $value) {
            $this->setTemplateValue($name, $value, $templateProcessor);
        }
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'template_types',
            'upload_destination',
            'template_file_directory',
            'colabora_http_client_options',
        ]);
        $resolver->setAllowedTypes('template_types', 'array');
    }
}
