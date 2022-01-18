<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class SearchService
{
    public function getFieldMatches(string $search)
    {
        preg_match('/^\d{2}-?\d{6}$/', $search, $caseNumberMatches);

        $result = [];
        if (1 === \count($caseNumberMatches)) {
            $match = $caseNumberMatches[0];

            // Add '-' if missing.
            if (8 === \strlen($match)) {
                $match = substr($match, 0, 2).'-'.substr($match, 2, 6);
            }

            $result['caseNumber'] = $match;
        }

        return $result;
    }

    public function applyFieldSearch(QueryBuilder $queryBuilder, array $matches): QueryBuilder
    {
        if (isset($matches['caseNumber'])) {
            $queryBuilder->orWhere('c.caseNumber = :search_case_number_alternative');
            $queryBuilder->setParameter(':search_case_number_alternative', $matches['caseNumber']);
        }

        return $queryBuilder;
    }
}
