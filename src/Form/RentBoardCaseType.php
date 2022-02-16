<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\RentBoardCase;
use App\Form\Embeddable\AddressLookupType;
use App\Service\IdentifierChoices;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class RentBoardCaseType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RentBoardCase::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Board $board */
        $board = $options['board'];

        $builder
            ->add('complainant', TextType::class, [
                'label' => $this->translator->trans('Complainant', [], 'case'),
            ])
            ->add('complainantIdentifierType', ChoiceType::class, [
                'label' => $this->translator->trans('Identifier type', [], 'case'),
                'choices' => IdentifierChoices::IDENTIFIER_TYPE_CHOICES,
            ])
            ->add('complainantIdentifier', TextType::class, [
                'label' => $this->translator->trans('Identifier', [], 'case'),
            ])
            ->add('complainantPhone', IntegerType::class, [
                'label' => $this->translator->trans('Complainant phone', [], 'case'),
            ])
            ->add('complainantAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Complainant address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up complainant address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('hasVacated', CheckboxType::class, [
                'label' => $this->translator->trans('Has vacated', [], 'case'),
                'required' => false,
            ])
            ->add('leaseAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Lease address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up lease address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('leaseType', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Big', [], 'case') => $this->translator->trans('Big', [], 'case'),
                    $this->translator->trans('Small', [], 'case') => $this->translator->trans('Small', [], 'case'),
                ],
                'label' => $this->translator->trans('Lease type', [], 'case'),
            ])
            ->add('complaintCategory', EntityType::class, [
                'class' => ComplaintCategory::class,
                'choices' => $board->getComplaintCategories(),
                'label' => $this->translator->trans('Complaint category', [], 'case'),
                'placeholder' => $this->translator->trans('Select a complaint category', [], 'case'),
            ])
            ->add('feePaid', CheckboxType::class, [
                'label' => $this->translator->trans('Fee paid', [], 'case'),
                'required' => false,
            ])
            ->add('leaseStarted', DateType::class, [
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'label' => $this->translator->trans('Lease started', [], 'case'),
                'required' => false,
            ])
            ->add('leaseSize', IntegerType::class, [
                'label' => $this->translator->trans('Lease size', [], 'case'),
                'required' => false,
            ])
            ->add('leaseAgreedRent', IntegerType::class, [
                'label' => $this->translator->trans('Lease agreed rent', [], 'case'),
                'required' => false,
            ])
            ->add('leaseInteriorMaintenance', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Tenant', [], 'case') => $this->translator->trans('Tenant', [], 'case'),
                    $this->translator->trans('Landlord', [], 'case') => $this->translator->trans('Landlord', [], 'case'),
                ],
                'label' => $this->translator->trans('Lease interior maintenance', [], 'case'),
                'required' => false,
            ])
            ->add('leaseRegulatedRent', IntegerType::class, [
                'label' => $this->translator->trans('Lease regulated rent', [], 'case'),
                'required' => false,
            ])
            ->add('leaseRentAtCollectionTime', IntegerType::class, [
                'label' => $this->translator->trans('Lease rent at collection time', [], 'case'),
                'required' => false,
            ])
            ->add('leaseSecurityDeposit', IntegerType::class, [
                'label' => $this->translator->trans('Lease security deposit', [], 'case'),
                'required' => false,
            ])
            ->add('prepaidRent', IntegerType::class, [
                'label' => $this->translator->trans('Prepaid rent', [], 'case'),
                'required' => false,
            ])
        ;
    }
}
