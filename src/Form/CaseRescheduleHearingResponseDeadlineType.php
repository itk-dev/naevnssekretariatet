<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseRescheduleHearingResponseDeadlineType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseEntity::class,
            'validation_groups' => ['hearing_response_deadline'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hearingResponseDeadline', DateType::class, [
                'label' => $this->translator->trans('Reschedule to', [], 'case'),
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'empty_data' => null,
            ])
        ;
    }
}
