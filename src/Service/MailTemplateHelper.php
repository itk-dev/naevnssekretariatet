<?php

namespace App\Service;

use App\Entity\MailTemplate;
use App\Entity\User;
use App\Exception\MailTemplateException;
use App\Repository\MailTemplateMacroRepository;
use App\Repository\MailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailTemplateHelper
{
    /**
     * The options.
     *
     * @var array
     */
    private $options;

    public function __construct(private MailTemplateRepository $mailTemplateRepository, private MailTemplateMacroRepository $mailTemplateMacroRepository, private EntityManagerInterface $entityManager, private SerializerInterface $serializer, private Filesystem $filesystem, private LoggerInterface $logger, private TokenStorageInterface $tokenStorage, private TranslatorInterface $translator, array $mailTemplateHelperOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($mailTemplateHelperOptions);
    }

    public function getMailTemplateTypeChoices(): array
    {
        $templateTypes = array_flip(array_map(static fn (array $spec) => $spec['label'], $this->options['template_types']));

        foreach ($templateTypes as $key => $value) {
            $translatedKey = $this->translator->trans($key, [], 'mail_template');
            $templateTypes[$translatedKey] = $value;
            unset($templateTypes[$key]);
        }

        ksort($templateTypes);

        return $templateTypes;
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
                } catch (MappingException $mappingException) {
                    // Cannot get repository for the class name. Continue to see if another class name succeeds.
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
     * @throws MailTemplateException
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
            // Handle line breaks in macro content.
            if (count($lines) > 1) {
                $element = new TextRun();
                foreach ($lines as $index => $line) {
                    if ($index > 0) {
                        $element->addTextBreak();
                    }
                    $element->addText($line);
                }
            } else {
                $element = $macro->getContent();
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

        // https://sdk.collaboraonline.com/docs/conversion_api.html
        try {
            $response = $client->request('POST', '/lool/convert-to/pdf', [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);
            $content = $response->getContent();
        } catch (ClientException|TransportException $exception) {
            $this->logger->critical(sprintf('Error talking to Collabora: %s', $exception->getMessage()), ['exception' => $exception]);
            throw new MailTemplateException(sprintf('Error rendering mail template %s', $mailTemplate->getName()), $exception->getCode(), $exception);
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
            // Convert entity to array.
            $data = json_decode($this->serializer->serialize($entity, 'json', ['groups' => ['mail_template']]), true);
            $values += $this->flatten($data);
        }

        // Add user info
        $user = $this->tokenStorage->getToken()?->getUser();
        if (null !== $user && $user instanceof User) {
            $values += $this->flatten([
                'user' => [
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                ],
            ]);
        }

        // Add some default values.
        $values['now'] = (new \DateTimeImmutable('now'))->format(\DateTimeImmutable::ATOM);
        $values['today'] = (new \DateTimeImmutable('today'))->format(\DateTimeImmutable::ATOM);

        foreach ($placeHolders as $placeHolder) {
            // Handle placeholders on the form «key»:«format», e.g.
            //   case.inspection_date:dd/mm/yyyy
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
        // Try to format the value as a date(time).
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

    private function setTemplateValue(string $search, $replace, TemplateProcessor $templateProcessor)
    {
        if ($replace instanceof AbstractElement) {
            // TemplateProcessor::setComplexValue() only replaces one (the
            // first) occurrence.
            // To prevent infinite loops (in the [unlikely] case that the
            // replacement value contains the search value) we replace at most
            // 10 times.
            $maxReplacements = 10;
            while (isset($templateProcessor->getVariableCount()[$search]) && --$maxReplacements >= 0) {
                $templateProcessor->setComplexValue($search, $replace);
            }
        } else {
            $templateProcessor->setValue($search, $replace);
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

    /**
     * Flatten array value.
     */
    private function flatten(array $value): array
    {
        // Serialize to csv to flatten array values and get keys separated by `.`, i.e.
        //   ['name' => ['given' => 'Anders', 'family' => 'And']]
        // will be converted to
        //   ['name.given' => 'Anders', 'name.family' => 'And']
        $csv = $this->serializer->serialize($value, 'csv');
        // We now have a csv string with two lines (the first is the header) that we split and parse.
        [$header, $row] = array_map('str_getcsv', explode(PHP_EOL, $csv, 2));
        /*
         * @psalm-suppress InvalidArgument
         *
         * Argument 1 of array_combine expects array<array-key, array-key>, non-empty-list<null|string> provided (see https://psalm.dev/004)
         */
        return array_combine($header, $row);
    }
}
