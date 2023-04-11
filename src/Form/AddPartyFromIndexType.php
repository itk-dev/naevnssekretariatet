<?php

namespace App\Form;

use App\Entity\Party;
use App\Service\PartyHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddPartyFromIndexType extends AbstractType
{
    /**
     * @var PartyHelper
     */
    private $partyHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(PartyHelper $partyHelper, TranslatorInterface $translator)
    {
        $this->partyHelper = $partyHelper;
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'case' => null,
            'type' => null,
        ]);

        $resolver->setRequired('party_choices');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $case = $options['case'];
        $choices = $options['party_choices'];
        $type = $options['type'];

        $builder
            ->add('partyToAdd', EntityType::class, [
                'class' => Party::class,
                'choices' => $choices,
                'multiple' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $this->partyHelper->getAllPartyTypes($case),
                'data' => $type,
            ])
            ->add('referenceNumber', TextType::class, [
                'label' => $this->translator->trans('Reference number', [], 'party'),
                'required' => false,
            ])
        ;
    }
}
