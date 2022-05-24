<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\ResidentComplaintBoardCase;
use App\Form\Embeddable\AddressLookupType;
use App\Form\Embeddable\IdentificationType;
use App\Repository\ComplaintCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResidentComplaintBoardCaseType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private ComplaintCategoryRepository $categoryRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResidentComplaintBoardCase::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Board $board */
        $board = $options['board'];

        $builder
            ->add('bringerIdentification', IdentificationType::class, [
                'label' => $this->translator->trans('Bringer', [], 'case'),
                'is_required' => true,
            ])
            ->add('lookupIdentifier', ButtonType::class, [
                'label' => $this->translator->trans('Find information from identifier', [], 'case'),
                'attr' => [
                    'class' => 'btn-primary btn identification-lookup',
                    'data-specifier' => 'bringer',
                ],
            ])
            ->add('bringerIsUnderAddressProtection', CheckboxType::class, [
                'label' => $this->translator->trans('!Is under address protection!', [], 'case'),
                'required' => false,
            ])
            ->add('bringer', TextType::class, [
                'label' => $this->translator->trans('Bringer name', [], 'case'),
            ])
            ->add('bringerPhone', IntegerType::class, [
                'label' => $this->translator->trans('Bringer phone', [], 'case'),
                'required' => false,
            ])
            ->add('bringerAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Bringer address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up bringer address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('hasVacated', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('Yes', [], 'case') => true,
                    $this->translator->trans('No', [], 'case') => false,
                ],
                'label' => $this->translator->trans('Has vacated', [], 'case'),
            ])
            ->add('copyAddress', ButtonType::class, [
                'label' => $this->translator->trans('Copy above address', [], 'case'),
                'attr' => [
                    'class' => 'btn-primary btn',
                ],
            ])
            ->add('leaseAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Lease address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up lease address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('complaintCategory', EntityType::class, [
                'class' => ComplaintCategory::class,
                'choices' => $this->categoryRepository->findComplaintCategoriesByBoard($board),
                'label' => $this->translator->trans('Complaint category', [], 'case'),
                'placeholder' => $this->translator->trans('Select a complaint category', [], 'case'),
            ])
            ->add('extraComplaintCategoryInformation', TextType::class, [
                'label' => $this->translator->trans('Extra complaint category information', [], 'case'),
                'required' => false,
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
            ->add('leaseRegulatedRent', ChoiceType::class, [
                'label' => $this->translator->trans('Lease regulated rent', [], 'case'),
                'choices' => [
                    $this->translator->trans('Yes', [], 'case') => true,
                    $this->translator->trans('No', [], 'case') => false,
                ],
                'placeholder' => $this->translator->trans('Select an option', [], 'case'),
                'required' => false,
            ])
            ->add('leaseRegulatedAt', DateType::class, [
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'label' => $this->translator->trans('Lease regulated at', [], 'case'),
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
