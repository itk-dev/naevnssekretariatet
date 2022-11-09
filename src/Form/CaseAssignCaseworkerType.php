<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseAssignCaseworkerType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseEntity::class,
            'available_caseworkers' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $caseworkers = $options['available_caseworkers'];
        array_push($caseworkers, null);

        $builder
            ->add('assignedTo', ChoiceType::class, [
                'label' => $this->translator->trans('Assign to', [], 'case'),
                'choices' => $caseworkers,
                'choice_label' => function ($key) {
                    if (null === $key) {
                        return $this->translator->trans('None', [], 'case');
                    }

                    return $key->getName();
                },
            ])
        ;
    }
}
