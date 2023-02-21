<?php

namespace App\Form;

use App\Entity\Municipality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class MunicipalitySelectorType extends AbstractType
{
    public function __construct(private readonly SessionInterface $session, private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'active_municipality' => null,
            'municipalities' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $municipalities = $options['municipalities'];
        $activeMunicipality = $options['active_municipality'];

        $builder->add('municipality', ChoiceType::class, [
            'choices' => $municipalities,
            'choice_label' => function (?Municipality $municipality) {
                return $municipality->getName();
            },
            'label' => $this->translator->trans('Show for', [], 'agenda'),
            'data' => $activeMunicipality,
        ]);
    }
}
