<?php

namespace App\Form;

use App\Entity\BoardMember;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaAddBoardMemberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('board_member_choices');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['board_member_choices'];

        $builder
            ->add('boardMemberToAdd', EntityType::class, [
                'class' => BoardMember::class,
                'choices' => $choices,
                'multiple' => true,
            ])
        ;
    }
}
