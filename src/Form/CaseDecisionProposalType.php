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
            ->add('decisionProposal', CKEditorType::class, [
                'label' => $this->translator->trans('Decision proposal', [], 'agenda_item'),
                'attr' => ['rows' => 6],
                'config' => [
                    'uiColor' => '#ffffff',
                    'toolbar' => 'editor',
                ],
            ])
        ;
    }
}
