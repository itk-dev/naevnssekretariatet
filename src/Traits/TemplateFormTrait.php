<?php

namespace App\Traits;

use App\Entity\MailTemplate;
use App\Form\MailTemplateCustomDataType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TemplateFormTrait
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function addTemplate(FormBuilderInterface $builder, array $availableTemplateChoices)
    {
        $templateChoices = [];

        foreach ($availableTemplateChoices as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder->add('template', EntityType::class, [
            'class' => MailTemplate::class,
            'placeholder' => $this->translator->trans('Choose a template', [], 'case'),
            'label' => $this->translator->trans('Mail template', [], 'case'),
            'choices' => $templateChoices,
        ]);

        $formModifier = function (FormInterface $form, ?MailTemplate $mailTemplate = null) use ($builder) {
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
    }
}
