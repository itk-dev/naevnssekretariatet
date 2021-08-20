<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PartyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('cpr')
            ->add('address')
            ->add('phoneNumber', IntegerType::class)
            ->add('journalNumber')
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