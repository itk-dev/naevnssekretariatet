<?php

namespace App\Form;

use App\Entity\InspectionLetter;
use App\Entity\Party;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
    }
}
