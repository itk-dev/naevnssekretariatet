<?php

namespace App\Form;

use App\Entity\AgendaCaseItem;
use App\Entity\AgendaManuelItem;
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

        $caseItemTranslated = $this->translator->trans('Case item', [], 'agenda');
        $manuelItemTranslated = $this->translator->trans('Manuel item', [], 'agenda');

        $builder->add('type', ChoiceType::class, [
            'choices' => [
                $caseItemTranslated => AgendaCaseItem::class,
                $manuelItemTranslated => AgendaManuelItem::class,
            ],
            'placeholder' => $this->translator->trans('Choose an agenda item type', [], 'agenda'),
        ]);

        $formModifier = function (FormInterface $form, ?string $type = null) use ($board) {
            if (null != $type) {
                $formClass = null;
                switch ($type) {
                    case AgendaCaseItem::class:
                        $formClass = AgendaCaseItemNewType::class;
                        $form->add('agendaItem', $formClass, [
                            'board' => $board,
                        ]);
                        break;
                    case AgendaManuelItem::class:
                        $formClass = AgendaManuelItemType::class;
                        $form->add('agendaItem', $formClass, [
                        ]);
                        break;
                    default:
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
