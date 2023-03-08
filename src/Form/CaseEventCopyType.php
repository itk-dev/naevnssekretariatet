<?php

namespace App\Form;

use App\Entity\CaseEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseEventCopyType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'case' => null,
            'suitableCases' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $options['case'];
        $suitableCases = $options['suitableCases'];

        $builder->add('cases', EntityType::class, [
            'choices' => $suitableCases,
            'class' => get_class($case),
            'label' => $this->translator->trans('Copy to', [], 'case_event'),
            'multiple' => true,
            'attr' => [
                'class' => 'select2',
                'data-placeholder' => $this->translator->trans('Select cases', [], 'case_event'),
            ],
            'required' => true,
            'help' => $this->translator->trans('Be aware, that this creates a link between the case event and the selected cases, meaning changes to the case event will be shown on all linked cases.', [], 'case_event'),
        ]);
    }
}
