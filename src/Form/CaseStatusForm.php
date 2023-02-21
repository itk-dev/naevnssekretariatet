<?php

namespace App\Form;

use App\Form\Model\CaseStatusFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseStatusForm extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseStatusFormModel::class,
            'current_status' => null,
            'available_statuses' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ChoiceType::class, [
            'choices' => $options['available_statuses'],
            'label' => $this->translator->trans('Change case status', [], 'case'),
            'placeholder' => $options['current_status'],
        ]);
        $builder->add('submit', SubmitType::class, [
            'label' => $this->translator->trans('Change status', [], 'case'),
        ]);
    }
}
