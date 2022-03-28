<?php

namespace App\Form\Embeddable;

use App\Entity\Embeddable\Identification;
use App\Service\IdentifierChoices;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class IdentificationType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identification::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => $this->translator->trans('Identification type', [], 'case'),
                'choices' => IdentifierChoices::IDENTIFIER_TYPE_CHOICES,
            ])
            ->add('identifier', TextType::class, [
                'label' => $this->translator->trans('Identification', [], 'case'),
                'attr' => [
                    'placeholder' => $this->translator->trans('Identifier', [], 'case'),
                ],
            ])
            ->add('pNumber', TextType::class, [
                'label' => $this->translator->trans('P-number', [], 'case'),
                'attr' => [
                    'placeholder' => $this->translator->trans('P-number', [], 'case'),
                ],
                'required' => false,
            ])
        ;
    }
}
