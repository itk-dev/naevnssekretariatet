<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseEventDocumentType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => null,
            'view_timezone' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = $options['choices'];

        $builder
            ->add('subject', TextType::class, [
                'label' => $this->translator->trans('Subject', [], 'case_event'),
            ])
            ->add('receivedAt', DateTimeType::class, [
                'label' => $this->translator->trans('Received at', [], 'case_event'),
                'widget' => 'single_text',
                'with_seconds' => true,
                'data' => new \DateTime('now'),
                'view_timezone' => $options['view_timezone'],
                'model_timezone' => 'UTC',
            ])
            ->add('senders', ChoiceType::class, [
                'label' => $this->translator->trans('Senders', [], 'case_event'),
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('additionalSenders', TextareaType::class, [
                'label' => $this->translator->trans('Senders', [], 'case_event'),
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
                'help' => $this->translator->trans('List of additional senders that are not parties on the case (one per line).', [], 'case_event'),
            ])
            ->add('recipients', ChoiceType::class, [
                'label' => $this->translator->trans('Recipients', [], 'case_event'),
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('additionalRecipients', TextareaType::class, [
                'label' => $this->translator->trans('Recipients', [], 'case_event'),
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
                'help' => $this->translator->trans('List of additional recipients that are not parties on the case (one per line).', [], 'case_event'),
            ])
        ;
    }
}
