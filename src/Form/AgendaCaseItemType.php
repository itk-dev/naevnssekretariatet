<?php

namespace App\Form;

use App\Entity\AgendaCaseItem;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Repository\CaseDocumentRelationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaCaseItemType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var CaseDocumentRelationRepository
     */
    private $relationRepository;

    public function __construct(CaseDocumentRelationRepository $relationRepository, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->relationRepository = $relationRepository;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AgendaCaseItem::class,
            'isCreateContext' => false,
            'relevantCase' => null,
        ]);

        $resolver->setAllowedTypes('isCreateContext', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCreateContext = $options['isCreateContext'];

        if (!$isCreateContext) {
            /** @var CaseEntity $case */
            $case = $options['relevantCase'];

            $documents = $this->relationRepository->findNonDeletedDocumentsByCase($case);
        }

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda_item'),
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
            ])
            ->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choice_label' => 'caseNumber',
                'label' => $this->translator->trans('Case', [], 'agenda_item'),
                'placeholder' => $this->translator->trans('Choose a case', [], 'agenda_item'),
            ])
            ->add('inspection', CheckboxType::class, [
                'label' => $this->translator->trans('Inspection', [], 'agenda_item'),
                'required' => false,
            ]);
        if (!$isCreateContext) {
//            $builder->add('documents', EntityType::class, [
//                'class' => Document::class,
//                'choices' => $documents,
//                'label' => $this->translator->trans('Documents', [], 'agenda_item'),
//                'expanded' => true,
//                'multiple' => true,
//            ]);
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => $this->translator->trans('Update agenda item', [], 'agenda_item'),
                ]);
        } else {
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => $this->translator->trans('Create agenda item', [], 'agenda_item'),
                ]);
        }
    }
}
