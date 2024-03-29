<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\FenceReviewCase;
use App\Form\Embeddable\AddressLookupType;
use App\Form\Embeddable\IdentificationType;
use App\Repository\ComplaintCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FenceReviewCaseType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator, private ComplaintCategoryRepository $categoryRepository)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FenceReviewCase::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var $board Board
         */
        $board = $options['board'];

        $builder
            ->add('receivedAt', DateType::class, [
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'label' => $this->translator->trans('Received at', [], 'case'),
            ])
            ->add('validatedAt', DateType::class, [
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'label' => $this->translator->trans('Validated at', [], 'case'),
                'required' => false,
            ])
            ->add('bringerIdentification', IdentificationType::class, [
                'label' => $this->translator->trans('Bringer', [], 'case'),
                'is_required' => false,
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
            ->add('bringerAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Bringer address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up bringer address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('bringerCadastralNumber', TextType::class, [
                'label' => $this->translator->trans('Bringer cadastral number', [], 'case'),
            ])
            ->add('accusedIdentification', IdentificationType::class, [
                'label' => $this->translator->trans('Accused', [], 'case'),
                'is_required' => false,
            ])
            ->add('lookupAccusedIdentifier', ButtonType::class, [
                'label' => $this->translator->trans('Find information from identifier', [], 'case'),
                'attr' => [
                    'class' => 'btn-primary btn identification-lookup',
                    'data-specifier' => 'accused',
                ],
            ])
            ->add('accusedIsUnderAddressProtection', CheckboxType::class, [
                'label' => $this->translator->trans('!Is under address protection!', [], 'case'),
                'required' => false,
            ])
            ->add('accused', TextType::class, [
                'label' => $this->translator->trans('Accused name', [], 'case'),
            ])
            ->add('accusedAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Accused address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up accused address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('accusedCadastralNumber', TextType::class, [
                'label' => $this->translator->trans('Accused cadastral number', [], 'case'),
                'required' => false,
            ])
            ->add('complaintCategories', EntityType::class, [
                'multiple' => true,
                'expanded' => true,
                'class' => ComplaintCategory::class,
                'choices' => $this->categoryRepository->findComplaintCategoriesByBoard($board),
                'label' => $this->translator->trans('Complaint categories', [], 'case'),
            ])
            ->add('extraComplaintCategoryInformation', TextType::class, [
                'label' => $this->translator->trans('Extra complaint category information', [], 'case'),
                'required' => false,
            ])
            ->add('conditions', TextareaType::class, [
                'label' => $this->translator->trans('Conditions', [], 'case'),
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('bringerClaim', TextareaType::class, [
                'label' => $this->translator->trans('Claim', [], 'case'),
                'attr' => [
                    'rows' => 6,
                ],
            ])
        ;
    }
}
