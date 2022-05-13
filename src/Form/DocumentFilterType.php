<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Repository\DocumentRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DocumentFilterType extends AbstractType
{
    public function __construct(private DocumentRepository $documentRepository, private TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'validation_groups' => ['filtering'],
                'case' => null,
            ])
            ->setRequired('case')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var CaseEntity $case */
        $case = $options['case'];

        $typeChoices = [];
        foreach ($this->documentRepository->getAvailableDocumentsForCase($case) as $document) {
            $typeChoices[$document->getType()] = $document->getType();
        }
        asort($typeChoices);

        $builder
            ->add('type', Filters\ChoiceFilterType::class, [
                'choices' => $typeChoices,
                'placeholder' => $this->translator->trans('Select document type', [], 'document'),
            ])
            ->add('documentName', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'placeholder' => $this->translator->trans('Search documents', [], 'document'),
                ],
            ])
        ;
    }
}
