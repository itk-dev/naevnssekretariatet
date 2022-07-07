<?php

namespace App\Form;

use App\Entity\HearingPostRequest;
use App\Entity\MailTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostRequestType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HearingPostRequest::class,
            'case_parties' => null,
            'mail_template_choices' => null,
            'available_case_documents' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $caseParties = $options['case_parties'];
        $availableTemplateChoices = $options['mail_template_choices'];

        $templateChoices = [];

        foreach ($availableTemplateChoices as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'case'),
                'help' => $this->translator->trans('Choose a title for the hearing post', [], 'case'),
            ])
            ->add('template', EntityType::class, [
                'class' => MailTemplate::class,
                'placeholder' => $this->translator->trans('Choose a template', [], 'case'),
                'label' => $this->translator->trans('Mail template', [], 'case'),
                'choices' => $templateChoices,
            ])
        ;

        $formModifier = function (FormInterface $form, MailTemplate $mailTemplate = null) use ($builder) {
            $form->add('customData', MailTemplateCustomDataType::class, [
                'label' => false,
                'template' => $mailTemplate,
                'data' => $builder->getData()->getCustomData(),
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getTemplate());
            }
        );

        $builder->get('template')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $template = $event->getForm()->getData();

                $formModifier($event->getForm()->getParent(), $template);
            }
        );

        $builder
            ->add('recipient', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a recipient', [], 'case'),
                'label' => $this->translator->trans('Recipient', [], 'case'),
                'choices' => $caseParties,
            ])
            ->add('attachments', CollectionType::class, [
                'label' => $this->translator->trans('Attach case documents', [], 'case'),
                'required' => false,
                'entry_type' => HearingPostAttachmentType::class,
                'entry_options' => [
                    'available_case_documents' => $options['available_case_documents'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                // Post update
                'by_reference' => false,
            ])
        ;
    }
}
