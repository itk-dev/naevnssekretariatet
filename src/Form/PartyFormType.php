<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Form\Embeddable\AddressLookupType;
use App\Service\IdentifierChoices;
use App\Service\PartyHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartyFormType extends AbstractType
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
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $options['case'];

        $builder
            ->add('name', TextType::class, [
                'label' => $this->translator->trans('Name', [], 'party'),
            ])
            ->add('identifierType', ChoiceType::class, [
                'label' => $this->translator->trans('Identifier type', [], 'party'),
                'choices' => IdentifierChoices::IDENTIFIER_TYPE_CHOICES,
            ])
            ->add('identifier', TextType::class, [
                'label' => $this->translator->trans('Identifier', [], 'party'),
            ])
            ->add('address', AddressLookupType::class, [
                'label' => $this->translator->trans('Address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('phoneNumber', IntegerType::class, [
                'label' => $this->translator->trans('Phone number', [], 'party'),
            ])
            ->add('journalNumber', TextType::class, [
                'label' => $this->translator->trans('Journal number', [], 'party'),
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => $this->translator->trans('Journal number', [], 'party'),
                'choices' => $this->partyHelper->getAllPartyTypes($case),
            ])
        ;
    }
}
