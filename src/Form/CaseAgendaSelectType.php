<?php

namespace App\Form;

use App\Entity\Agenda;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseAgendaSelectType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'hasActiveAgenda' => null,
            'agendas' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hasActiveAgenda = $options['hasActiveAgenda'];
        $agendas = $options['agendas'];

        if ($hasActiveAgenda) {
            return;
        }
        $builder
            ->add('agenda', EntityType::class, [
                'class' => Agenda::class,
                'choices' => $agendas,
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Add case to agenda', [], 'case'),
            ])
        ;
    }
}
