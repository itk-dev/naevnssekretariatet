<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseRescheduleFinishProcessDeadlineType extends AbstractType
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
//            'validation_groups' => ['process_finish'],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $builder->getData();

        $builder
            ->add('finishProcessingDeadline', DateType::class, [
                'label' => $this->translator->trans('Reschedule to', [], 'case'),
                'widget' => 'single_text',
                'input_format' => 'dd-MM-yyyy',
                'constraints' => [
                    new GreaterThanOrEqual(
                        $case->getFinishHearingDeadline()
                    ),
                ],
            ])
        ;
    }
}
