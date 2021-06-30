<?php

namespace App\Service;

use App\Repository\CaseEntityRepository;

class CaseManager
{
    /**
     * @var CaseEntityRepository
     */
    private $caseRepository;

    public function __construct(CaseEntityRepository $caseRepository)
    {
        $this->caseRepository = $caseRepository;
    }

    public function generateCaseNumber(): string
    {
        $case = $this->caseRepository->findLatestCase();

        $date = new \DateTime();
        $currentYear = $date->format('Y');

        // Check if first case
        if (null === $case) {
            return $currentYear.'-0001';
        }

        $latestCaseYear = $case->getCreatedAt()->format('Y');

        // Check if we have entered a new year
        if ($currentYear !== $latestCaseYear) {
            return $currentYear.'-0001';
        }

        return $latestCaseYear.'-'.$this->getIncrementedCaseCounter($case->getCaseNumber());
    }

    public function getIncrementedCaseCounter(string $caseNumber): string
    {
        $positionOfDash = strpos($caseNumber, '-');
        $counter = (int) substr($caseNumber, $positionOfDash + 1);

        $incrementedCounter = $counter + 1;

        return str_pad(strval($incrementedCounter), 4, '0', STR_PAD_LEFT);
    }
}
