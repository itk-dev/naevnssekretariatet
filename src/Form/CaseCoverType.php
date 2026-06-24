<?php

namespace App\Form;

use App\Entity\MailTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseCoverType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'mail_template_choices' => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $templateChoices = [];
        foreach ($options['mail_template_choices'] as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder->add('template', EntityType::class, [
            'class' => MailTemplate::class,
            'label' => $this->translator->trans('Choose a case cover template', [], 'case'),
            'choices' => $templateChoices,
        ]);
    }
}
