<?php

namespace App\Form;

use App\Entity\HearingPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostCreateType extends AbstractType
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
            'data_class' => HearingPost::class,
            'case_parties' => null,
            'mail_templates_choices' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $caseParties = $options['case_parties'];
        $templateChoices = $options['mail_template_choices'];

        $builder
            ->add('template', ChoiceType::class, [
                'label' => $this->translator->trans('Mail template', [], 'case'),
                'choices' => $caseParties,
            ])
            ->add('recipient', ChoiceType::class, [
                'label' => $this->translator->trans('Recipient', [], 'case'),
                'choices' => $caseParties,
            ])
            ->add('content', TextareaType::class, [
                'label' => $this->translator->trans('Content', [], 'case'),
            ])
        ;
    }
}
