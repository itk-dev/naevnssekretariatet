<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\Board;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaCreateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agenda::class,
            'municipality' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $municipality = $options['municipality'];

        $builder
            ->add('board', EntityType::class, [
                'class' => Board::class,
                'query_builder' => function (EntityRepository $er) use ($municipality) {
                    return $er->createQueryBuilder('b')
                        ->where('b.municipality = :municipality')
                        ->setParameter('municipality', $municipality->getId()->toBinary())
                        ->orderBy('b.name', 'ASC');
                },
                'choice_label' => 'name',
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
