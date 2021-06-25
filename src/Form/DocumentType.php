<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType
{
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
                'label' => 'Document name:',
            ])
            ->add('type', null, [
                'label' => 'Document type:',
            ])
            ->add('filename', FileType::class, [
                'label' => 'Upload file',
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
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ]),
                ],
            ])
            ->add('uploadDocument', SubmitType::class, ['label' => 'Upload file'])
        ;
    }
}
