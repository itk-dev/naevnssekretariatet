<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\Board;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaType extends AbstractType
{
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
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Open' => 'Open',
                    'Full' => 'Full',
                    'Finished' => 'Finished',
                ],
            ])
            ->add('remarks', TextareaType::class, [
                'required' => false,
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd-MM-yyyy',
                'attr' => ['class' => 'form-control js-datetimepicker'],
                //'format' => 'j F y'
            ])
            ->add('start')
            ->add('end')
        ;
    }
}
