<?php

namespace App\Form;

use App\Entity\Party;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPartyFromIndexType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('party_choices');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['party_choices'];

        $builder
            ->add('partyToAdd', EntityType::class, [
                'class' => Party::class,
                'choices' => $choices,
                'multiple' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Tenant' => 'Tenant',
                    'Tentant (representative)' => 'Representative',
                    'Landlord' => 'Landlord',
                    'Landlord (administrator)' => 'Administrator',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Add Party']);
    }
}
