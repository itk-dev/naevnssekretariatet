<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Repository\DocumentRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
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
            ->add('query', Filters\TextFilterType::class, [
                'condition_pattern' => FilterOperands::STRING_CONTAINS,
                'attr' => [
                    'placeholder' => $this->translator->trans('Search documents', [], 'document'),
                ],
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    assert($filterQuery instanceof ORMQuery);
                    $properties = ['documentName', 'originalFileName'];
                    $expr = $filterQuery->getQueryBuilder()->expr();
                    $expression = $expr->orX();
                    foreach ($properties as $property) {
                        $expression->add(
                            $filterQuery->getExpressionBuilder()->stringLike('d.'.$property, $values['value'], $values['condition_pattern'] ?? FilterOperands::STRING_CONTAINS)
                        );
                    }

                    return $filterQuery->createCondition($expression);
                },
            ])
        ;
    }
}
