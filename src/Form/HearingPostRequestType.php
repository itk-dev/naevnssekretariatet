<?php

namespace App\Form;

use App\Entity\HearingPostRequest;
use App\Traits\TemplateFormTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostRequestType extends AbstractType
{
    use TemplateFormTrait;

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

        $builder
            ->add('title', TextType::class, [
                'label' => $this->translator->trans('Title', [], 'case'),
                'help' => $this->translator->trans('Choose a title for the hearing post', [], 'case'),
            ])
        ;

        $this->addTemplate($builder, $availableTemplateChoices);

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
