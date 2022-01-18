<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class SearchService
{
    /**
     * Get matches between fields and the search string.
     */
    public function getFieldMatches(string $search): array
    {
        $result = [];

        // Match case number with or without a dash after first two digits
        if (preg_match('/^\d{2}-?\d{6}$/', $search, $caseNumberMatches)) {
            $match = $caseNumberMatches[0];

            // Add '-' if missing.
            if (8 === \strlen($match)) {
                $match = substr($match, 0, 2).'-'.substr($match, 2, 6);
            }

            $result['caseNumber'] = $match;
        }

        return $result;
    }

    /**
     * Updates QueryBuilder based on matches.
     */
    public function applyFieldSearch(QueryBuilder $queryBuilder, array $matches): QueryBuilder
    {
        if (isset($matches['caseNumber'])) {
            $queryBuilder->orWhere('c.caseNumber = :search_case_number');
            $queryBuilder->setParameter(':search_case_number', $matches['caseNumber']);
        }

        return $queryBuilder;
    }
}
