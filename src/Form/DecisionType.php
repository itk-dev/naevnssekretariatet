<?php

namespace App\Form;

use App\Entity\Decision;
use App\Entity\Party;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

class DecisionType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Decision::class,
            'available_recipients' => null,
            'available_case_documents' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableRecipients = $options['available_recipients'];

        $builder->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'case'),
                'help' => $this->translator->trans('Choose a title for the decision', [], 'case'),
            ])
            ->add('recipients', EntityType::class, [
                'class' => Party::class,
                'label' => $this->translator->trans('Recipients', [], 'case'),
                'choices' => $availableRecipients,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('filename', FileType::class, [
                'label' => $this->translator->trans('Upload decision', [], 'case'),
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('Please upload a pdf', [], 'case'),
                    ]),
                ],
            ])
            ->add('attachments', CollectionType::class, [
                'label' => $this->translator->trans('Attach case documents', [], 'case'),
                'required' => false,
                'entry_type' => DecisionAttachmentType::class,
                'entry_options' => [
                    'available_case_documents' => $options['available_case_documents'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                // Post update
                'by_reference' => false,
            ])
        ;
    }
}
