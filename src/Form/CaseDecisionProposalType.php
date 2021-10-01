<?php

namespace App\Form;

use App\Entity\CaseDecisionProposal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseDecisionProposalType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
            ->add('decisionProposal', TextareaType::class, [
                'label' => $this->translator->trans('Decision proposal', [], 'agenda_item'),
                'attr' => ['rows' => 10],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('Update proposal', [], 'agenda_item'),
            ]);
    }
}
