<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaItemType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $board = $options['board'];

        $caseItemTranslated = $this->translator->trans('Case item', [], 'agenda_item');
        $manuelItemTranslated = $this->translator->trans('Manuel item', [], 'agenda_item');

        $builder->add('type', ChoiceType::class, [
            'choices' => [
                $caseItemTranslated => $caseItemTranslated,
                $manuelItemTranslated => $manuelItemTranslated,
            ],
            'placeholder' => $this->translator->trans('Choose an agenda item type', [], 'agenda_item'),
        ]);

        $formModifier = function (FormInterface $form, string $type = null) use ($manuelItemTranslated, $caseItemTranslated, $board) {
            if (null != $type) {
                $formClass = null;
                switch ($type) {
                    case $caseItemTranslated:
                        $formClass = AgendaCaseItemType::class;
                        $form->add('agendaItem', $formClass, [
                            'board' => $board,
                            'isCreateContext' => true,
                        ]);
                        break;
                    case $manuelItemTranslated:
                        $formClass = AgendaManuelItemType::class;
                        $form->add('agendaItem', $formClass, [
                            'isCreateContext' => true,
                        ]);
                        break;
                    default:
                        var_dump($type);
                        $message = 'Type was not chosen correctly';
                        throw new \Exception($message);
                }
            } else {
                $form->add('agendaItem', HiddenType::class);
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event->getForm(), $event->getData());
            }
        );

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $board = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $board);
            }
        );
    }
}
