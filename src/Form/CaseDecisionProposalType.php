<?php

namespace App\Form;

use App\Entity\CaseDecisionProposal;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseDecisionProposalType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CaseDecisionProposal::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('decisionProposal', CKEditorType::class, [
                'label' => $this->translator->trans('Decision proposal', [], 'agenda'),
                'attr' => ['rows' => 6],
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'editor',
                ],
            ])
        ;
    }
}
