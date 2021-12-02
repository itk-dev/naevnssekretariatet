<?php

namespace App\Form\Embeddable;

use App\Entity\Embeddable\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddressType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, [
                'label' => $this->translator->trans('Street', [], 'address'),
            ])
            ->add('number', IntegerType::class, [
                'label' => $this->translator->trans('Number', [], 'address'),
            ])
            ->add('side', TextType::class, [
                'label' => $this->translator->trans('Side', [], 'address'),
            ])
            ->add('floor', TextType::class, [
                'label' => $this->translator->trans('Floor', [], 'address'),
            ])
            ->add('postalCode', IntegerType::class, [
                'label' => $this->translator->trans('Postal Code', [], 'address'),
            ])
            ->add('city', TextType::class, [
                'label' => $this->translator->trans('City', [], 'address'),
            ])
        ;
    }
}
