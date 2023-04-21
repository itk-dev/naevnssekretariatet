<?php

namespace App\Service;

use App\Entity\AgendaBroadcast;
use App\Entity\CaseEntity;
use App\Entity\HearingPost;
use App\Entity\HearingPostResponse;
use App\Entity\HearingRecipient;
use App\Entity\InspectionLetter;
use App\Entity\MailTemplate;
use App\Entity\User;
use App\Exception\MailTemplateException;
use App\Form\MailTemplateCustomDataType;
use App\Repository\CasePartyRelationRepository;
use App\Repository\MailTemplateMacroRepository;
use App\Repository\MailTemplateRepository;
use App\Service\MailTemplate\ComplexMacro;
use App\Service\MailTemplate\ComplexMacroHelper;
use App\Service\MailTemplate\LinkedTemplateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\MappingException;
use Gedmo\Timestampable\Timestampable;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
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

    private string $placeholderPattern = '/\$\{(?P<key>[^}]+)\}/';

    public function __construct(private MailTemplateRepository $mailTemplateRepository, private MailTemplateMacroRepository $mailTemplateMacroRepository, private EntityManagerInterface $entityManager, private SerializerInterface $serializer, private Filesystem $filesystem, private LoggerInterface $logger, private TokenStorageInterface $tokenStorage, private TranslatorInterface $translator, private ComplexMacroHelper $macroHelper, private CasePartyRelationRepository $relationRepository, array $mailTemplateHelperOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($mailTemplateHelperOptions);
    }

    public function getMailTemplateTypeChoices(): array
    {
        $templateTypes = array_flip(array_map(static fn (array $spec) => $spec['label'], $this->options['template_types']));

        $translatedTemplateTypes = [];

        foreach ($templateTypes as $key => $value) {
            $translatedKey = $this->translator->trans($key, [], 'mail_template');
            $translatedTemplateTypes[$translatedKey] = $value;
        }

        ksort($translatedTemplateTypes);

        return $translatedTemplateTypes;
    }

    /**
     * Get entity for previewing a mail template.
     *
     * @return mixed The entity
     *
     * @throws \RuntimeException
     */
    public function getPreviewEntity(MailTemplate $mailTemplate, string $entityType = null, string $entityId = null)
    {
        $classNames = $this->getTemplateEntityClassNames($mailTemplate) ?? [];
        if (null !== $entityType) {
            $this->validateEntityType($mailTemplate, $entityType);
            $classNames = [$entityType];
        }
        foreach ($classNames as $className) {
            try {
                $repository = $this->entityManager->getRepository($className);
                $entity = null !== $entityId ? $repository->find($entityId) : $repository->findOneBy([]);
                if (null !== $entity) {
                    return $entity;
                }
            } catch (MappingException $mappingException) {
                // Cannot get repository for the class name. Continue to see if another class name succeeds.
            }
        }

        throw new MailTemplateException(sprintf('Cannot get preview entity for mail template %s of type %s', $mailTemplate->getName(), $mailTemplate->getType()));
    }

    /**
     * Get valid entities for previewing a mail template.
     *
     * @return array|mixed The entities
     *
     * @throws \RuntimeException
     */
    public function getPreviewEntities(MailTemplate $mailTemplate)
    {
        $classNames = $this->getTemplateEntityClassNames($mailTemplate) ?? [];

        $entities = [];
        foreach ($classNames as $className) {
            try {
                $repository = $this->entityManager->getRepository($className);
                // Get at most 20 entities.
                $entities[] = $repository->findBy([], null, 20);
            } catch (MappingException $mappingException) {
                // Cannot get repository for the class name. Continue to see if another class name succeeds.
            }
        }

        $entities = array_merge(...$entities);

        usort($entities, static fn ($a, $b) => (string) $a <=> (string) $b);

        return $entities;
    }

    /**
     * @return string[]|array|null
     */
    public function getTemplateEntityClassNames(MailTemplate $mailTemplate): ?array
    {
        $spec = $this->options['template_types'][$mailTemplate->getType()] ?? null;

        return isset($spec['entity_class_names']) && is_array($spec['entity_class_names'])
            ? $spec['entity_class_names'] : null;
    }

    public function getTemplateData(MailTemplate $mailTemplate, $entity, bool $expandMacros = true): array
    {
        $templateFileName = $this->getTemplateFile($mailTemplate);
        $templateProcessor = new TemplateProcessor($templateFileName);

        $values = $this->getValues($entity, $templateProcessor, $mailTemplate);

        $listValues = $this->getComplexMacros($entity, $values);
        foreach ($listValues as $name => $macro) {
            $values[$name] = sprintf('(%s)', $macro->getDescription());
        }

        $macroValues = $this->getMacroValues($mailTemplate);
        foreach ($macroValues as $macro => $value) {
            if ($value instanceof TextRun) {
                $value = implode('', array_map(static function (AbstractElement $element) {
                    if ($element instanceof TextBreak) {
                        return PHP_EOL;
                    }
                    if ($element instanceof Text) {
                        return $element->getText();
                    }
                    throw new MailTemplateException(sprintf('Unhandled element: %s', get_class($element)));
                }, $value->getElements()));
            }
            if ($expandMacros) {
                $value = preg_replace_callback(
                    $this->placeholderPattern,
                    static function (array $matches) use ($values) {
                        return $values[$matches['key']] ?? $matches[0];
                    },
                    $value
                );
            }
            $values[$macro] = $value;
        }

        return $values;
    }

    public function renderMailTemplate(MailTemplate $mailTemplate, $entity = null, array $options = [
        'format' => 'pdf',
    ]): string
    {
        $options = (new OptionsResolver())
                 ->setDefault('format', 'pdf')
                 ->setAllowedValues('format', ['docx', 'pdf'])
                 ->resolve($options)
        ;

        // Expand all macros.
        $templateFileName = $this->getTemplateFile($mailTemplate);

        $templateProcessor = new TemplateProcessor($templateFileName);
        $macroValues = $this->getMacroValues($mailTemplate);
        foreach ($macroValues as $macro => $value) {
            $this->setTemplateValue($macro, $value, $templateProcessor);
        }

        // Render images.
        $templateFileName = $templateProcessor->save();
        $templateProcessor = new TemplateProcessor($templateFileName);
        $this->filesystem->remove($templateFileName);

        $this->renderImages($entity, $templateProcessor);

        // Set the final values.
        $templateFileName = $templateProcessor->save();
        // https://phpword.readthedocs.io/en/latest/templates-processing.html
        $templateProcessor = new LinkedTemplateProcessor($templateFileName);
        $this->filesystem->remove($templateFileName);

        // For some reason we have to render images again to make images inside
        // and outside macros show up.
        $this->renderImages($entity, $templateProcessor);

        $values = $this->getValues($entity, $templateProcessor, $mailTemplate);

        if (null !== $entity) {
            $this->validateEntityType($mailTemplate, $entity);
            $macroValues = $this->getComplexMacros($entity, $values, $templateProcessor);
            foreach ($macroValues as $name => $value) {
                $element = $value->getElement();

                if ($this->isBlockElement($element)) {
                    $templateProcessor->setComplexBlock($name, $element);
                } else {
                    if ($element instanceof Link) {
                        $templateProcessor->addLink($element);
                    }
                    $templateProcessor->setComplexValue($name, $element);
                }
            }
        }

        $this->setTemplateValues($values, $templateProcessor);
        $processedFileName = $templateProcessor->save();

        if ('docx' === $options['format']) {
            return $processedFileName;
        }

        $client = HttpClient::create($this->options['libreoffice_http_client_options']);
        $formFields = [
            'data' => DataPart::fromPath($processedFileName),
        ];
        $formData = new FormDataPart($formFields);

        try {
            $response = $client->request('POST', '/convert-to/pdf', [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);
            $content = $response->getContent();
        } catch (ClientException|TransportException $exception) {
            $this->logger->critical(sprintf('Error talking to Libreoffice: %s', $exception->getMessage()), ['exception' => $exception]);
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
        return $this->mailTemplateRepository->findBy(['type' => $type, 'isArchived' => false], ['name' => 'ASC']);
    }

    private static array $blockElementClasses = [
        Table::class,
    ];

    private function isBlockElement(AbstractElement $element)
    {
        return in_array(get_class($element), self::$blockElementClasses, true);
    }

    /**
     * Get values from an entity.
     *
     * @param $entity
     *
     * @return array|false|mixed|string[]
     */
    private function getValues(?object $entity, TemplateProcessor $templateProcessor, MailTemplate $mailTemplate)
    {
        $placeHolders = [$templateProcessor->getVariables()];

        // Add placeholders used in macros.
        $templateType = $mailTemplate->getType();
        $macros = $this->mailTemplateMacroRepository->findByTemplateType($templateType);
        foreach ($macros as $macro) {
            if (preg_match_all($this->placeholderPattern, $macro->getContent(), $matches)) {
                $placeHolders[] = $matches['key'];
            }
        }

        // Flatten and make unique.
        $placeHolders = array_unique(array_merge(...$placeHolders));
        $imagePlaceholders = [];

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
            $case = $this->getCase($entity);

            // Convert entity to array.
            $data = json_decode($this->serializer->serialize($entity, 'json', ['groups' => ['mail_template']]), true);

            // Make hearing, agenda and case data easily available.
            if ($entity instanceof HearingRecipient) {
                $hearingPost = $entity->getHearingPostRequest();

                $data += json_decode($this->serializer->serialize($hearingPost->getHearing(), 'json', ['groups' => ['mail_template']]), true);

                $relation = $this->relationRepository->findOneBy(['case' => $case->getId()->toBinary(), 'party' => $entity->getRecipient()->getId()->toBinary()]);

                if (!$relation) {
                    throw new MailTemplateException('Could not find relation between party and case.');
                }

                $relationData = json_decode($this->serializer->serialize($relation, 'json', ['groups' => ['mail_template']]), true);

                $data += ['relation' => $relationData];

                // For convenience, we add the same relation data with relevant prefix to help users.
                $data['recipient'] += $relationData;
            } elseif ($entity instanceof HearingPostResponse) {
                $data += json_decode($this->serializer->serialize($entity->getHearing(), 'json', ['groups' => ['mail_template']]), true);

                $relation = $this->relationRepository->findOneBy(['case' => $case->getId()->toBinary(), 'party' => $entity->getSender()->getId()->toBinary()]);

                if (!$relation) {
                    throw new MailTemplateException('Could not find relation between party and case.');
                }

                $relationData = json_decode($this->serializer->serialize($relation, 'json', ['groups' => ['mail_template']]), true);

                $data += ['relation' => $relationData];

                // For convenience, we add the same relation data with relevant prefix to help users.
                $data['sender'] += $relationData;
            } elseif ($entity instanceof AgendaBroadcast) {
                $data += json_decode($this->serializer->serialize($entity->getAgenda(), 'json', ['groups' => ['mail_template']]), true);
            }
            if (null !== $case) {
                // If the entity itself is a case it has already been added to the data.
                if ($case !== $entity) {
                    $data += json_decode($this->serializer->serialize($case, 'json', ['groups' => ['mail_template']]), true);
                }
            }

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
        if ($entity instanceof Timestampable) {
            $values['createdAt'] = $entity->getCreatedAt()->format(\DateTimeImmutable::ATOM);
            $values['updatedAt'] = $entity->getUpdatedAt()->format(\DateTimeImmutable::ATOM);
        }

        foreach ($placeHolders as $placeHolder) {
            // Handle placeholders on the form «key»|«filter»(«argument»), e.g.
            //   case.inspection_date:dd/mm/yyyy
            if (preg_match('/^(?P<key>.+)\|(?P<filter>[a-z]+)\((?P<argument>.+)\)$/', $placeHolder, $matches)) {
                [, $key, $filter, $argument] = $matches;
                if (isset($values[$key])) {
                    $argument = trim($argument, '\'"');
                    $value = $values[$key];
                    $values[$placeHolder] = match ($filter) {
                        'append' => empty($value) ? '' : $value.$argument,
                        'prepend' => empty($value) ? '' : $argument.$value,
                        'format' => $this->formatValue($value, $argument),
                        default => $value,
                    };
                }
            }
        }

        // Set empty string values for all template variables without a value.
        $values += array_map(static function () { return ''; }, $templateProcessor->getVariableCount());

        return $values;
    }

    private function renderImages($entity, TemplateProcessor $templateProcessor)
    {
        $case = $this->getCase($entity);
        if (null !== $case) {
            $signatureFilename = $case->getAssignedTo()?->getSignatureFilename() ?? null;
            if (null !== $signatureFilename) {
                $placeholder = 'assignedTo.signature';
                $filename = rtrim($this->options['user_signatures_file_directory'], '/').'/'.$signatureFilename;
                if (file_exists($filename)) {
                    $templateProcessor->setImageValue($placeholder, [
                        'path' => $filename,
                        'height' => $this->options['user_signature_height'],
                        // Setting the width to the empty string will keep the image aspect ratio
                        'width' => '',
                    ]);
                }
            }
        }
    }

    /**
     * Get case related to entity or the entity itself if it's a case.
     */
    private function getCase($entity): ?CaseEntity
    {
        if ($entity instanceof CaseEntity) {
            return $entity;
        } elseif ($entity instanceof HearingRecipient) {
            return $entity->getHearingPostRequest()->getHearing()->getCaseEntity();
        } elseif ($entity instanceof HearingPost) {
            return $entity->getHearing()->getCaseEntity();
        } elseif ($entity instanceof InspectionLetter) {
            return $entity->getAgendaCaseItem()?->getCaseEntity();
        }

        return null;
    }

    private function getMacroValues(MailTemplate $mailTemplate): array
    {
        $values = [];

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
                $element = $lines[0];
            }

            $values[$macro->getMacro()] = $element;
        }

        return $values;
    }

    /**
     * @return array|ComplexMacro[]
     */
    private function getComplexMacros(object $entity, array $values, TemplateProcessor $templateProcessor = null): array
    {
        $macros = [];

        $linkKeys = ['board.email', 'board.url'];
        foreach ($linkKeys as $key) {
            $url = $values[$key] ?? null;
            if (null !== $url) {
                $text = $url;
                if (filter_var($url, FILTER_VALIDATE_EMAIL)) {
                    $url = 'mailto:'.$url;
                }

                $macros[$key.'.link'] = new ComplexMacro(
                    $this->macroHelper->createLink($url, $text),
                    $text
                );
            }
        }

        return $macros + $this->macroHelper
                ->buildMacros($entity)
        ;
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
        // Catch new lines in strings
        if (is_string($replace)) {
            $lines = explode(PHP_EOL, trim($replace));
            // Encode special chars
            $lines = array_map('htmlspecialchars', $lines);
            if (count($lines) > 1) {
                $replace = new TextRun();
                foreach ($lines as $index => $line) {
                    if ($index > 0) {
                        $replace->addTextBreak();
                    }
                    $replace->addText($line);
                }
            } else {
                $replace = $lines[0];
            }
        }

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
            'user_signatures_file_directory',
            'user_signature_height',
            'libreoffice_http_client_options',
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

    public function getCustomFields(MailTemplate $template)
    {
        if (empty($template->getCustomFields())) {
            return [];
        }

        $rawCustomFields = array_filter(
            array_map(
                'trim',
                explode(
                    PHP_EOL,
                    $template->getCustomFields()
                )
            )
        );

        $customFields = [];
        foreach ($rawCustomFields as $rawCustomField) {
            // Match «name»|«label»|«type» where «type» is optional.
            if (preg_match('/^(?P<name>[^|]+)\s*\|\s*(?P<label>[^|]+)\s*(:?\|\s*(?P<type>.+))?/', $rawCustomField, $matches)) {
                // Name must be a valid form element name.
                $name = preg_replace('/[^\w_]+/i', '_', $matches['name']);
                $customFields[$name] = [
                    'label' => $matches['label'],
                    'type' => match ($matches['type'] ?? null) {
                        MailTemplateCustomDataType::TYPE_TEXTAREA => MailTemplateCustomDataType::TYPE_TEXTAREA,
                        default => MailTemplateCustomDataType::TYPE_TEXT
                    },
                ];
            }
        }

        return $customFields;
    }

    /**
     * Validate that a mail template can handle an entity or entity type.
     *
     * @return void
     *
     * @throws MailTemplateException
     */
    private function validateEntityType(MailTemplate $mailTemplate, string|object $entity)
    {
        $entityType = is_string($entity) ? $entity : get_class($entity);
        $classNames = $this->getTemplateEntityClassNames($mailTemplate) ?? [];
        foreach ($classNames as $className) {
            if (is_a($entityType, $className, true)) {
                return;
            }
        }

        throw new MailTemplateException(sprintf('Invalid entity type %s for template %s; only instances of %s allowed', $entityType, $mailTemplate->getName(), implode(', ', $classNames)));
    }
}
