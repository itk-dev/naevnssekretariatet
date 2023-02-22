<?php

namespace App\Form;

use App\Entity\BoardMember;
use App\Exception\BoardMemberRoleException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaAddBoardMemberType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('board_member_choices');
        $resolver->setRequired('board');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['board_member_choices'];
        $board = $options['board'];

        $builder
            ->add('boardMemberToAdd', EntityType::class, [
                'class' => BoardMember::class,
                'choices' => $choices,
                'choice_label' => function (BoardMember $boardMember) use ($board) {
                    $roles = $boardMember->getBoardRoles()->filter(fn ($role) => $role->getBoard() === $board);

                    if (1 != sizeof($roles)) {
                        $message = sprintf('Board member: %s, is assigned %s roles on board %s.', $boardMember->getName(), sizeof($roles), $board->getName());
                        throw new BoardMemberRoleException($message);
                    }
                    // TODO: Some sort of check on size of roles - should just be one element
                    return $boardMember->getName().' - '.$roles->current()->getTitle();
                },
                'multiple' => true,
            ])
        ;
    }
}
