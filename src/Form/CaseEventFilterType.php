<?php

namespace App\Form;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Repository\CaseEventRepository;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type as Filters;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CaseEventFilterType extends AbstractType
{
    public function __construct(private CaseEventRepository $caseEventRepository, private TranslatorInterface $translator)
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

        $partyChoices = [];
        foreach ($case->getCasePartyRelation() as $relation) {
            $partyChoices[$relation->getParty()->getName()] = $relation->getParty()->getName();
        }
        asort($partyChoices);

        $typeChoices = [];
        foreach ($this->caseEventRepository->getAvailableCaseEventsForCase($case) as $caseEvent) {
            $typeChoices[$caseEvent->getSubject()] = $caseEvent->getSubject();
        }
        asort($typeChoices);

        $builder
            ->add('category', Filters\ChoiceFilterType::class, [
                'choices' => [
                    CaseEvent::CATEGORY_INCOMING => CaseEvent::CATEGORY_INCOMING,
                    CaseEvent::CATEGORY_OUTGOING => CaseEvent::CATEGORY_OUTGOING,
                    CaseEvent::CATEGORY_NOTE => CaseEvent::CATEGORY_NOTE,
                ],
                'placeholder' => $this->translator->trans('Select case event category', [], 'case_event'),
            ])
            ->add('query', Filters\ChoiceFilterType::class, [
                'placeholder' => $this->translator->trans('Select sender/recipient', [], 'case_event'),
                'choices' => $partyChoices,
                'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                    if (empty($values['value'])) {
                        return null;
                    }

                    assert($filterQuery instanceof ORMQuery);
                    $expr = $filterQuery->getQueryBuilder()->expr();
                    $expression = $expr->orX();
                    $expression->add(
                        $filterQuery->getExpressionBuilder()->stringLike('ce_relation_part.name', $values['value'], $values['condition_pattern'] ?? FilterOperands::STRING_EQUALS)
                    );

                    return $filterQuery->createCondition($expression);
                },
            ])
        ;
    }
}
