<?php

namespace App\Form;

use App\Entity\AgendaManuelItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaManuelItemType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AgendaManuelItem::class,
            'isCreateContext' => false,
        ]);

        $resolver->setAllowedTypes('isCreateContext', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCreateContext = $options['isCreateContext'];

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda_item'),
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description', [], 'agenda_item'),
            ])
            ->add('startTime', TimeType::class, [
                'label' => $this->translator->trans('Start time', [], 'agenda_item'),
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
            ->add('endTime', TimeType::class, [
                'label' => $this->translator->trans('End time', [], 'agenda_item'),
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
            ->add('meetingPoint', TextType::class, [
                'label' => $this->translator->trans('Meeting point', [], 'agenda_item'),
            ]);

        if (!$isCreateContext) {
            $builder->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Update agenda item', [], 'agenda_item'),
            ]);
        } else {
            $builder->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Create agenda item', [], 'agenda_item'),
            ]);
        }
    }
}
