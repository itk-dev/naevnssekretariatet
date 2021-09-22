<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\Board;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaCreateType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agenda::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('board', EntityType::class, [
                'class' => Board::class,
                'choice_label' => 'name',
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
//                'html5' => false,
//                'attr' => ['class' => 'js-datepicker'],
//                'format' => 'dd-MM-yyyy',
            ])
            ->add('start')
            ->add('end')
        ;
    }
}
