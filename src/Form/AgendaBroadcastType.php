<?php

namespace App\Form;

use App\Entity\AgendaBroadcast;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
    }
}
