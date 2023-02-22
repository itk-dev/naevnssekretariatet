<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseAgendaStatusType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaseEntity::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('isReadyForAgenda', CheckboxType::class, [
            'label' => $this->translator->trans('Is ready for agenda', [], 'case'),
            'required' => false,
        ]);
        $builder->add('shouldBeInspected', CheckboxType::class, [
            'label' => $this->translator->trans('Should be inspected', [], 'case'),
            'required' => false,
        ]);
    }
}
