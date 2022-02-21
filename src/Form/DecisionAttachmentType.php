<?php

namespace App\Form;

use App\Entity\DecisionAttachment;
use App\Entity\Document;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DecisionAttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document', EntityType::class, [
                'class' => Document::class,
                'choices' => $options['available_case_documents'],
                'placeholder' => new TranslatableMessage('Select a document', [], 'case'),
                'empty_data' => null,
            ])
            ->add('remove', ButtonType::class, [
                'label' => new TranslatableMessage('Remove document', [], 'case'),
                'attr' => [
                    'data-remove-item' => null,
                    'class' => 'btn-danger',
                ],
            ])
            ->add('position', HiddenType::class, [
                'data' => 1,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DecisionAttachment::class,
            'available_case_documents' => null,
        ]);
    }
}
