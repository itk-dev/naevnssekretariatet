<?php

namespace App\Form;

use App\Entity\AgendaBroadcast;
use App\Entity\MailTemplate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaBroadcastType extends AbstractType
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
            'data_class' => AgendaBroadcast::class,
            'mail_template_choices' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableTemplateChoices = $options['mail_template_choices'];

        $templateChoices = [];

        foreach ($availableTemplateChoices as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda'),
                'help' => $this->translator->trans('Choose a title for the broadcast', [], 'agenda'),
            ])
            ->add('template', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a template', [], 'agenda'),
                'label' => $this->translator->trans('Mail template', [], 'agenda'),
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
    }
}
