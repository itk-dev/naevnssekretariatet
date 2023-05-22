<?php

namespace App\Form;

use App\Entity\HearingBriefing;
use App\Entity\Party;
use App\Traits\TemplateFormTrait;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Contracts\Translation\TranslatorInterface;

class BriefingType extends AbstractType
{
    use TemplateFormTrait;

    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HearingBriefing::class,
            'case_parties' => null,
            'mail_template_choices' => null,
            'preselects' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'case'),
            ])
            ->add('recipients', EntityType::class, [
                'class' => Party::class,
                'label' => $this->translator->trans('Recipients', [], 'case'),
                'choices' => $options['case_parties'],
                'choice_label' => function ($key, $value) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true,
                'data' => $options['preselects'],
                'mapped' => false,
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => new TranslatableMessage('Your briefing letter must have at least one recipient', [], 'validators')]),
                ],
            ])
        ;

        $this->addTemplate($builder, $options['mail_template_choices']);
    }
}
