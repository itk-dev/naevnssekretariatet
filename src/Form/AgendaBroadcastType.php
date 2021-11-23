<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaBroadcastType extends AbstractType
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
        $builder
            ->add('template', ChoiceType::class, [
                'label' => $this->translator->trans('Template', [], 'agenda'),
                'choices' => [
                    'Agenda broadcast template 1' => 'agenda_broadcast_template_1',
                    'Agenda broadcast  template 2' => 'agenda_broadcast_template_2',
                    'Agenda broadcast  template 3' => 'agenda_broadcast_template_3',
                ],
                'placeholder' => $this->translator->trans('Select a template', [], 'agenda'),
            ])
            ->add('contents', TextareaType::class, [
                'label' => $this->translator->trans('Contents', [], 'agenda'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Broadcast agenda', [], 'agenda'),
            ])
        ;
    }
}
