<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaCaseItemType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
            ->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choice_label' => 'caseNumber',
                'label' => $this->translator->trans('Case', [], 'agenda_item'),
            ])
            ->add('inspection', CheckboxType::class, [
                'label' => $this->translator->trans('Inspection', [], 'agenda_item'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Create agenda item', [], 'agenda_item'),
            ]);
    }
}
