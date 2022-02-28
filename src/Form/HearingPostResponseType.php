<?php

namespace App\Form;

use App\Entity\HearingPostResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class HearingPostResponseType extends AbstractType
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
            'data_class' => HearingPostResponse::class,
            'case_parties' => null,
            'available_case_documents' => null,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $caseParties = $options['case_parties'];
        $availableCaseDocuments = $options['available_case_documents'];

        $documentChoices = [];

        foreach ($availableCaseDocuments as $document) {
            $documentChoices[$document->getDocumentName()] = $document;
        }

        $builder
            ->add('sender', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a sender', [], 'case'),
                'label' => $this->translator->trans('Sender', [], 'case'),
                'choices' => $caseParties,
            ])
            ->add('document', ChoiceType::class, [
                'placeholder' => $this->translator->trans('Choose a document', [], 'case'),
                'label' => $this->translator->trans('Document', [], 'case'),
                'required' => true,
                'choices' => $documentChoices,
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
