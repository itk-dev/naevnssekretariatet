<?php


namespace App\Form;


use App\Entity\Board;
use App\Entity\Municipality;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class CaseTypeSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('municipality', EntityType::class, [
                'class' => Municipality::class,
                'choice_label' => 'name',
                'placeholder' => '',
            ]);

        $formModifier = function (FormInterface $form, Municipality $municipality = null) {
            $boards = null === $municipality ? [] : $municipality->getBoards();

            $form->add('board', EntityType::class, [
                'class' => Board::class,
                'placeholder' => '',
                'choices' => $boards,
            ]);
        };


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();

                /** @var Municipality $municipality */
                $municipality = $event->getData();

                $formModifier($form, $municipality);
            }
        );

        $builder->get('municipality')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $form = $event->getForm();

                /** @var Municipality $municipality */
                $municipality = $form->get('municipality');

                $formModifier($form->getParent(), $municipality);
            }
        );

//        $builder->addEventListener(
//            FormEvents::PRE_SET_DATA,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//
//                /** @var Board $board */
//                $board = $form->get('board');
//
//                if (null !== $board) {
//                    $form->add('caseEntity', $board->getCaseFormType());
//                }
//            }
//        );
    }
}