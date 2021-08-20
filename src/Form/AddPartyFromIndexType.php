<?php

namespace App\Form;

use App\Entity\Party;
use App\Repository\PartyRepository;
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
        $resolver->setRequired('party_repository');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PartyRepository $partyRepository */
        $partyRepository = $options['party_repository'];

        $choices = $partyRepository->findBy(['isPartOfPartIndex' => true]);

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
            ->add('addParty', SubmitType::class, ['label' => 'Add Party']);
    }
}