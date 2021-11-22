<?php

namespace App\Form;

use App\Entity\AgendaCaseItem;
use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Repository\CaseEntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
     * @var CaseEntityRepository
     */
    private $caseRepository;

    public function __construct(CaseEntityRepository $caseRepository, TranslatorInterface $translator)
    {
        $this->caseRepository = $caseRepository;
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AgendaCaseItem::class,
            'isCreateContext' => false,
            'board' => null,
        ]);

        $resolver->setAllowedTypes('isCreateContext', 'bool');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCreateContext = $options['isCreateContext'];

        if ($isCreateContext) {
            /** @var Board $board */
            $board = $options['board'];

            $casesWithBoardAndWithoutActiveAgenda = $this->caseRepository->findReadyCasesWithoutActiveAgendaByBoard($board);
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
            ]);

        if ($isCreateContext) {
            $builder->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choices' => $casesWithBoardAndWithoutActiveAgenda,
                'choice_label' => function ($caseEntity) {
                    $caseNumber = $caseEntity->getCaseNumber();
                    $isInspection = $caseEntity->getShouldBeInspected();
                    $complaint = $caseEntity->getComplaintCategory()->getName();
                    $address = $caseEntity->getComplainantAddress();

                    if ($isInspection) {
                        $label = $caseNumber.' - '.$this->translator->trans('Inspection', [], 'agenda_item').' - '.$complaint.' - '.$address;
                    } else {
                        $label = $caseNumber.' - '.$complaint.' - '.$address;
                    }

                    return $label;
                },
                'label' => $this->translator->trans('Case', [], 'agenda_item'),
                'placeholder' => $this->translator->trans('Choose a case', [], 'agenda_item'),
            ]);
        } else {
            $builder->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choice_label' => 'caseNumber',
                'label' => $this->translator->trans('Case', [], 'agenda_item'),
                'disabled' => true,
            ]);
        }
    }
}
