<?php

namespace App\Form;

use App\Entity\AgendaBroadcast;
use App\Traits\TemplateFormTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgendaBroadcastType extends AbstractType
{
    use TemplateFormTrait;

    public function __construct(private readonly TranslatorInterface $translator)
    {
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

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'agenda'),
                'help' => $this->translator->trans('Choose a title for the broadcast', [], 'agenda'),
            ])
        ;

        $this->addTemplate($builder, $availableTemplateChoices);
    }
}
