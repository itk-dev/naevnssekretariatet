<?php

namespace App\Form;

use App\Entity\Agenda;
use Symfony\Component\Form\AbstractType;
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
            ->add('board')
            ->add('status')
            ->add('remarks')
            ->add('date')
            ->add('start')
            ->add('end')
        ;
    }
}