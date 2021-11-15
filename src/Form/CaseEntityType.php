<?php

namespace App\Form;

use App\Entity\Board;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseEntityType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('board', EntityType::class, [
            'class' => Board::class,
            'label' => $this->translator->trans('Board', [], 'case'),
            'placeholder' => $this->translator->trans('Choose a board', [], 'case'),
        ]);

        $formModifier = function (FormInterface $form, Board $board = null) {
            if (null != $board) {
                $caseFormType = $board->getCaseFormType();
                $form->add('caseEntity', 'App\\Form\\'.$caseFormType, [
                    'board' => $board,
                ]);
            } else {
                $form->add('caseEntity', HiddenType::class);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event->getForm(), $event->getData());
            }
        );

        $builder->get('board')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $board = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $board);
            }
        );
    }
}
