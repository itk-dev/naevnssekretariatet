<?php

namespace App\Form;

use App\Entity\AgendaCaseItem;
use App\Entity\CaseEntity;
use App\Repository\CaseEntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaCaseItemEditType extends AbstractType
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
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('caseEntity', EntityType::class, [
                'class' => CaseEntity::class,
                'choice_label' => 'caseNumber',
                'label' => $this->translator->trans('Case', [], 'agenda'),
                'disabled' => true,
            ])
        ;
    }
}
