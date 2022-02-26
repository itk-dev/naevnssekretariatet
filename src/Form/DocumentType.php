<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\UploadedDocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
                'label' => $this->translator->trans('Upload file', [], 'documents'),
                'help' => $this->translator->trans('Max file size: 10mb. File formats accepted: .pdf, .txt, .mp4, .jpeg, .png, .doc, .xls', [], 'documents'),
                'mapped' => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
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
}
