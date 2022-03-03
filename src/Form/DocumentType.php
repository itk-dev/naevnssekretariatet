<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\UploadedDocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private int $maxFileSize)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ])
            ->add('files', FileType::class, [
                'label' => $this->translator->trans('Files', [], 'documents'),
                'help' => new TranslatableMessage('Upload one or more files. Max file size: {size}. File formats accepted: .pdf, .txt, .mp4, .jpeg, .png, .doc, .xls', ['{size}' => $this->formatBytes($this->maxFileSize)], 'documents'),
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
                                    'application/vnd.ms-excel',
                                    'text/plain',
                                    'image/jpeg',
                                    'image/png',
                                    'video/mp4',
                                ],
                                'mimeTypesMessage' => $this->translator->trans('Please upload a valid document', [], 'documents'),
                            ]),
                        ],
                    ]),
                ],
            ])
        ;
    }

    public function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1000));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1000, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
