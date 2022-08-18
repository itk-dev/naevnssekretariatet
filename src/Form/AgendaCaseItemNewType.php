<?php

namespace App\Form;

use App\Entity\AgendaCaseItem;
use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Service\BoardHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaCaseItemNewType extends AbstractType
{
    public function __construct(private BoardHelper $boardHelper, private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AgendaCaseItem::class,
            'board' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Board $board */
        $board = $options['board'];

        $builder
            ->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choices' => $this->boardHelper->getCasesReadyForAgendaByBoardAndSuitableBoards($board),
                'choice_label' => function (CaseEntity $caseEntity) {
                    $caseNumber = $caseEntity->getCaseNumber();
                    $isInspection = $caseEntity->shouldBeInspected();
                    $complaint = $caseEntity->getComplaintCategory()->getName();
                    $address = $caseEntity->getSortingAddress();

                    if ($isInspection) {
                        $label = $caseNumber.' - '.$this->translator->trans('Inspection', [], 'agenda').' - '.$complaint.' - '.$address;
                    } else {
                        $label = $caseNumber.' - '.$complaint.' - '.$address;
                    }

                    return $label;
                },
                'label' => $this->translator->trans('Case', [], 'agenda'),
                'placeholder' => $this->translator->trans('Choose a case', [], 'agenda'),
            ])
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda'),
            ])
            ->add('startTime', TimeType::class, [
                'label' => $this->translator->trans('Start time', [], 'agenda'),
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
            ->add('endTime', TimeType::class, [
                'label' => $this->translator->trans('End time', [], 'agenda'),
                'input' => 'datetime',
                'widget' => 'single_text',
                'input_format' => 'H:i',
            ])
            ->add('meetingPoint', TextType::class, [
                'label' => $this->translator->trans('Meeting point', [], 'agenda'),
                'required' => false,
            ])
        ;
    }
}
