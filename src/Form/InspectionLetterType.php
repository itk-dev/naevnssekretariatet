<?php

namespace App\Form;

use App\Entity\InspectionLetter;
use App\Entity\MailTemplate;
use App\Entity\Party;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Contracts\Translation\TranslatorInterface;

class InspectionLetterType extends AbstractType
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
            'data_class' => InspectionLetter::class,
            'mail_template_choices' => null,
            'available_recipients' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $availableRecipients = $options['available_recipients'];

        $availableTemplateChoices = $options['mail_template_choices'];
        $templateChoices = [];

        foreach ($availableTemplateChoices as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda'),
                'help' => $this->translator->trans('Choose a title for the inspection letter', [], 'agenda'),
            ])
            ->add('recipients', EntityType::class, [
                'class' => Party::class,
                'label' => $this->translator->trans('Recipients', [], 'case'),
                'choices' => $availableRecipients,
                'choice_label' => function ($key, $value) {
                    return $value;
                },
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => new TranslatableMessage('Your inspection letter must have at least one recipient', [], 'validators')]),
                ],
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
