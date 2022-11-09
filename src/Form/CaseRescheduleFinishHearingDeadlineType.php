<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseRescheduleFinishHearingDeadlineType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseEntity::class,
            'validation_groups' => ['hearing_finish'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('finishHearingDeadline', DateType::class, [
                'label' => $this->translator->trans('Reschedule to', [], 'case'),
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'empty_data' => null,
            ])
        ;
    }
}
