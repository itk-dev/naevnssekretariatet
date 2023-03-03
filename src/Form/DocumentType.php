<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\UploadedDocumentType;
use App\Service\PartyHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentType extends AbstractType
{
    public const CASE_EVENT_OPTION_NO = 'No';
    public const CASE_EVENT_OPTION_YES = 'Yes';

    public function __construct(private TranslatorInterface $translator, private EntityManagerInterface $entityManager, private PartyHelper $partyHelper, private int $maxFileSize)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
            'case' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        $isNewDocument = !$this->entityManager->contains($entity);

        $builder
            ->add('documentName', null, [
                'label' => $this->translator->trans('Document name', [], 'documents'),
                'help' => $this->translator->trans('Provide a recognizable name for the document', [], 'documents'),
            ])
            ->add('type', EntityType::class, [
                'class' => UploadedDocumentType::class,
                'label' => $this->translator->trans('Document type', [], 'documents'),
                'choice_label' => 'name',
                'help' => $this->translator->trans('Provide a document type', [], 'documents'),
                'placeholder' => $this->translator->trans('Select document type', [], 'documents'),
            ])
        ;

        // Add a transformer from string to UploadedDocumentType and back.
        $builder->get('type')
            ->addModelTransformer(new CallbackTransformer(
                function ($name) {
                    return $this->entityManager->getRepository(UploadedDocumentType::class)->findOneBy(['name' => $name]);
                },
                function (?UploadedDocumentType $type) {
                    return $type?->getName();
                }
            ))
        ;

        // Allow files and case event only on new documents.
        if ($isNewDocument) {
            $builder
                ->add('files', FileType::class, [
                    'label' => $this->translator->trans('Files', [], 'documents'),
                    'help' => new TranslatableMessage('Upload one or more files. Max file size: {size}. File formats accepted: .pdf, .txt, .mp4, .jpeg, .png, .docx, .xlsx, .msg',
                        ['{size}' => $this->getMinimumMaximumFileSizeRestriction()], 'documents'),
                    'mapped' => false,
                    'multiple' => true,
                    'constraints' => [
                        new All([
                            'constraints' => [
                                new File([
                                    'maxSize' => $this->maxFileSize,
                                    'mimeTypes' => [
                                        'application/pdf',
                                        'application/x-pdf',
                                        'application/msword',
                                        'text/plain',
                                        'image/jpeg',
                                        'image/png',
                                        'video/mp4',
                                        'application/vnd.ms-outlook',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ],
                                    'mimeTypesMessage' => $this->translator->trans('Please upload a valid document', [],
                                        'documents'),
                                ]),
                            ],
                        ]),
                    ],
                ])
            ;
        }

        $builder->add('createCaseEvent', ChoiceType::class, [
            'choices' => [
                self::CASE_EVENT_OPTION_NO => self::CASE_EVENT_OPTION_NO,
                self::CASE_EVENT_OPTION_YES => self::CASE_EVENT_OPTION_YES,
            ],
            'choice_translation_domain' => 'documents',
            'data' => self::CASE_EVENT_OPTION_NO,
            'label' => $this->translator->trans('Create case event?', [], 'documents'),
            'mapped' => false,
        ]);

        $formModifier = function (FormInterface $form, ?string $createCaseEvent = null) use ($options) {
            if (self::CASE_EVENT_OPTION_YES === $createCaseEvent) {
                $form->add('caseEvent', CaseEventDocumentType::class, [
                    'mapped' => false,
                    'choices' => $this->partyHelper->getTransformedRelevantPartiesByCase($options['case']),
                ]);
            } else {
                $form->add('caseEvent', HiddenType::class, [
                    'mapped' => false,
                ]);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. Document
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getType() ?? null);
            }
        );

        $builder->get('createCaseEvent')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $createCaseEvent = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback function!
                $formModifier($event->getForm()->getParent(), $createCaseEvent);
            }
        );
    }

    public function getMinimumMaximumFileSizeRestriction(): string
    {
        static $maxSize = -1;

        if ($maxSize < 0) {
            // Start with post_max_size.
            $postMaxSize = $this->parseSize(ini_get('post_max_size'));
            if ($postMaxSize > 0) {
                $maxSize = $postMaxSize;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $uploadMax = $this->parseSize(ini_get('upload_max_filesize'));
            if ($uploadMax > 0 && $uploadMax < $maxSize) {
                $maxSize = $uploadMax;
            }
        }

        return $this->formatBytes($maxSize);
    }

    public function parseSize(string $size): float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    public function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
