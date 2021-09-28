<?php

namespace App\Form;

use App\Entity\AgendaManuelItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startTime', TextType::class, [
                'label' => $this->translator->trans('Start time', [], 'agenda_item'),
            ])
            ->add('endTime', TextType::class, [
                'label' => $this->translator->trans('End time', [], 'agenda_item'),
            ])
            ->add('meetingPoint', TextType::class, [
                'label' => $this->translator->trans('Meeting point', [], 'agenda_item'),
            ])
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda_item'),
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description', [], 'agenda_item'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Create agenda item', [], 'agenda_item'),
            ]);
    }
}
