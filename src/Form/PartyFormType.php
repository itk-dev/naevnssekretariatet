<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Service\PartyHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartyFormType extends AbstractType
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
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $options['case'];

        $builder
            ->add('name')
            ->add('cpr')
            ->add('address')
            ->add('phoneNumber', IntegerType::class)
            ->add('journalNumber', null, [
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => $this->partyHelper->getAllPartyTypes($case),
            ])
        ;
    }
}
