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
            ->add('complainantIdentification', IdentificationType::class, [
                'label' => false,
            ])
            ->add('lookupIdentifier', ButtonType::class, [
                'label' => $this->translator->trans('Find information from identifier', [], 'case'),
                'attr' => [
                    'class' => 'btn-primary btn identification-lookup',
                    'data-specifier' => 'complainant',
                ],
            ])
            ->add('complainant', TextType::class, [
                'label' => $this->translator->trans('Complainant', [], 'case'),
            ])
            ->add('complainantAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Complainant address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up complainant address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('complainantCadastralNumber', TextType::class, [
                'label' => $this->translator->trans('Complainant cadastral number', [], 'case'),
            ])
            ->add('accusedIdentification', IdentificationType::class, [
                'label' => false,
            ])
            ->add('lookupAccusedIdentifier', ButtonType::class, [
                'label' => $this->translator->trans('Find information from identifier', [], 'case'),
                'attr' => [
                    'class' => 'btn-primary btn identification-lookup',
                    'data-specifier' => 'accused',
                ],
            ])
            ->add('accused', TextType::class, [
                'label' => $this->translator->trans('Accused', [], 'case'),
            ])
            ->add('accusedAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Accused address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up accused address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('accusedCadastralNumber', TextType::class, [
                'label' => $this->translator->trans('Accused cadastral number', [], 'case'),
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
            ->add('conditions', TextareaType::class, [
                'label' => $this->translator->trans('Conditions', [], 'case'),
                'attr' => [
                    'rows' => 8,
                ],
            ])
            ->add('complainantClaim', TextareaType::class, [
                'label' => $this->translator->trans('Claim', [], 'case'),
                'attr' => [
                    'rows' => 6,
                ],
            ])
        ;
    }
}
