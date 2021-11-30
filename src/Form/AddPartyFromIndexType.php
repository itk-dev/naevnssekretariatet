<?php

namespace App\Form;

use App\Entity\Party;
use App\Service\PartyHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddPartyFromIndexType extends AbstractType
{
    /**
     * @var PartyHelper
     */
    private $partyHelper;

    public function __construct(PartyHelper $partyHelper)
    {
        $this->partyHelper = $partyHelper;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'case' => null,
        ]);

        $resolver->setRequired('party_choices');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $case = $options['case'];
        $choices = $options['party_choices'];

        $builder
            ->add('partyToAdd', EntityType::class, [
                'class' => Party::class,
                'choices' => $choices,
                'multiple' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $this->partyHelper->getAllPartyTypes($case),
            ]);
    }
}
