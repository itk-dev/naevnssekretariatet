<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseMoveType extends AbstractType
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
            'boards' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $boards = $options['boards'];

        $builder
            ->add('board', ChoiceType::class, [
                'label' => $this->translator->trans('Move to', [], 'case'),
                'choices' => $boards,
                'choice_label' => function ($key) {
                    return $key->getName();
                },
            ])
        ;
    }
}
