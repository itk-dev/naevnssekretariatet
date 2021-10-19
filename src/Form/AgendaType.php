<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\Board;
use App\Service\AgendaStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
                'disabled' => true,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    AgendaStatus::Open => AgendaStatus::Open,
                    AgendaStatus::Full => AgendaStatus::Full,
                    AgendaStatus::Finished => AgendaStatus::Finished,
                ],
            ])
            ->add('remarks', TextareaType::class, [
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
            ])
            ->add('start', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
            ->add('end', TimeType::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
        ;
    }
}
