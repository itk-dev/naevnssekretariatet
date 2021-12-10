<?php

namespace App\Service;

use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

class FilterHelper
{
    public function applyFilterWithUuids(QueryInterface $filterQuery, $field, $values): ?ConditionInterface
    {
        if (empty($values['value'])) {
            return null;
        }

        $paramName = sprintf('p_%s', str_replace('.', '_', $field));

        // expression that represent the condition
        $expression = $filterQuery->getExpr()->eq($field, ':'.$paramName);

        // expression parameters
        // Added ->getId()->toBinary() to handle Uuid

        $parameters = [$paramName => $values['value']->getId()->toBinary()]; // [ name => value ]

        return $filterQuery->createCondition($expression, $parameters);
    }
}
