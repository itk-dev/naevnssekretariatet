<?php

namespace App\Form;

use App\Entity\InspectionLetter;
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

class InspectionLetterType extends AbstractType
{
    use TemplateFormTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
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

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda'),
                'help' => $this->translator->trans('Choose a title for the inspection letter', [], 'agenda'),
            ])
            ->add('recipients', EntityType::class, [
                'class' => Party::class,
                'label' => $this->translator->trans('Recipients', [], 'case'),
                'choices' => $availableRecipients,
                'choice_label' => fn($key, $value) => $value,
                'multiple' => true,
                'expanded' => true,
                'constraints' => [
                    new Count(['min' => 1, 'minMessage' => new TranslatableMessage('Your inspection letter must have at least one recipient', [], 'validators')]),
                ],
            ])
        ;

        $this->addTemplate($builder, $availableTemplateChoices);
    }
}
