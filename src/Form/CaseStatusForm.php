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
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseStatusFormModel::class,
            'available_statuses' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ChoiceType::class, [
            'choices' => $options['available_statuses'],
            'label' => $this->translator->trans('Change case status', [], 'case'),
            'placeholder' => $this->translator->trans('Choose new case status', [], 'case'),
        ]);
        $builder->add('submit', SubmitType::class, [
            'label' => $this->translator->trans('Change status', [], 'case'),
        ]);
    }
}
