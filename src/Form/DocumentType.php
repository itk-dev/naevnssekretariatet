<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\UploadedDocumentType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            ])
            ->add('type', EntityType::class, [
                'class' => UploadedDocumentType::class,
                'label' => $this->translator->trans('Document type', [], 'documents'),
                'choice_label' => 'name',
            ])
            ->add('filename', FileType::class, [
                'label' => $this->translator->trans('Upload file', [], 'documents'),
                'mapped' => false,
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
                        'mimeTypesMessage' => 'Please upload a valid document',
                    ]),
                ],
            ])
        ;
    }
}
