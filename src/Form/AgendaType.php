<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\SubBoard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add('subBoard', EntityType::class, [
                'class' => SubBoard::class,
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
                'input' => 'datetime',
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
