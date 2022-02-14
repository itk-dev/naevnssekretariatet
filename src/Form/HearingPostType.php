<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\HearingPost;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostType extends AbstractType
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
            'data_class' => HearingPost::class,
            'case_parties' => null,
            'mail_template_choices' => null,
            'available_case_documents' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $caseParties = $options['case_parties'];
        $availableTemplateChoices = $options['mail_template_choices'];
        $availableCaseDocuments = $options['available_case_documents'];

        $templateChoices = [];

        foreach ($availableTemplateChoices as $template) {
            $templateChoices[$template->getName()] = $template;
        }

        $builder
            ->add('template', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a template', [], 'case'),
                'label' => $this->translator->trans('Mail template', [], 'case'),
                'choices' => $templateChoices,
            ])
            ->add('recipient', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a recipient', [], 'case'),
                'label' => $this->translator->trans('Recipient', [], 'case'),
                'choices' => $caseParties,
            ])
            ->add('content', TextareaType::class, [
                'label' => $this->translator->trans('Content', [], 'case'),
            ])
            ->add('documents', EntityType::class, [
                'class' => Document::class,
                'choices' => $availableCaseDocuments,
                'placeholder' => $this->translator->trans('Choose documents', [], 'case'),
                'label' => $this->translator->trans('Attach case documents', [], 'case'),
                'multiple' => true,
                'by_reference' => false,
                'expanded' => true,
                'required' => false,
            ])
        ;
    }
}
