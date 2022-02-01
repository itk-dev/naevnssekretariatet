<?php

namespace App\Form;

use App\Entity\Board;
use App\Entity\Municipality;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $builder->add('municipality', EntityType::class, [
            'class' => Municipality::class,
            'label' => $this->translator->trans('Municipality', [], 'case'),
            'placeholder' => $this->translator->trans('Choose a municipality', [], 'case'),
        ]);

        $formModifier = function (FormInterface $form, Municipality $municipality = null, FormBuilderInterface $builder) {
            if (null != $municipality) {
//                $builder->getForm()->add('board', EntityType::class, [
//                    'class' => Board::class,
//                    'label' => $this->translator->trans('Board', [], 'case'),
//                    'placeholder' => $this->translator->trans('Choose a board', [], 'case'),
//                ]);



                $form->add('board', ChoiceType::class, [
                    'choices' => $municipality->getBoards(),
                    'label' => $this->translator->trans('Board', [], 'case'),
                    'placeholder' => $this->translator->trans('Choose a board', [], 'case'),
                    'choice_label' => fn (Board $board) => $board->getName(),
                ]);
            } else {
//                $builder->getForm()->add('board', HiddenType::class);
                $form->add('board', HiddenType::class);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier, $builder) {
                $formModifier($event->getForm(), $event->getData(), $builder);
            }
        );

        $builder->get('municipality')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier, $builder) {
                $municipality = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $municipality, $builder);
            }
        );
    }
}
