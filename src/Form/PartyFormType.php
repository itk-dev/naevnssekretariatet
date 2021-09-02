<?php

namespace App\Form;

use App\Entity\Party;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('party_action');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var string $partyRepository */
        $action = $options['party_action'];
        $label = $action . ' Party';

        $builder
            ->add('name')
            ->add('cpr')
            ->add('address')
            ->add('phoneNumber', IntegerType::class)
            ->add('journalNumber', null, [
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Tenant' => 'Tenant',
                    'Tentant (representative)' => 'Representative',
                    'Landlord' => 'Landlord',
                    'Landlord (administrator)' => 'Administrator',
                ],
            ])
            //->add('isPartOfPartIndex')
            ->add('save', SubmitType::class, ['label' => $label]);
    }
}