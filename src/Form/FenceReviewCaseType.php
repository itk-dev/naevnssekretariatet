<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Entity\FenceReviewCase;
use App\Form\Embeddable\AddressLookupType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class FenceReviewCaseType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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

        $identifierTypeChoices = [
            'CPR' => 'CPR',
            'CVR' => 'CVR',
        ];

        $builder
            ->add('complainant', TextType::class, [
                'label' => $this->translator->trans('Complainant', [], 'case'),
            ])
            ->add('complainantIdentifierType', ChoiceType::class, [
                'label' => $this->translator->trans('Identifier type', [], 'case'),
                'choices' => $identifierTypeChoices,
            ])
            ->add('complainantIdentifier', TextType::class, [
                'label' => $this->translator->trans('Identifier', [], 'case'),
            ])
            ->add('complainantAddress', AddressLookupType::class, [
                'label' => $this->translator->trans('Complainant address', [], 'case'),
                'lookup-placeholder' => $this->translator->trans('Look up complainant address', [], 'case'),
                'lookup-help' => $this->translator->trans('Look up an address to fill out the address fields', [], 'case'),
            ])
            ->add('complainantCadastralNumber', TextType::class, [
                'label' => $this->translator->trans('Complainant cadastral number', [], 'case'),
            ])
            ->add('accused', TextType::class, [
                'label' => $this->translator->trans('Accused', [], 'case'),
            ])
            ->add('accusedIdentifierType', ChoiceType::class, [
                'label' => $this->translator->trans('Identifier type', [], 'case'),
                'choices' => $identifierTypeChoices,
            ])
            ->add('accusedIdentifier', TextType::class, [
                'label' => $this->translator->trans('Identifier', [], 'case'),
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
                'choices' => $board->getComplaintCategories(),
                'label' => $this->translator->trans('Complaint category', [], 'case'),
                'placeholder' => $this->translator->trans('Select a complaint category', [], 'case'),
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
