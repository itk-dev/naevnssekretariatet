<?php

namespace App\Service;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Repository\CaseEntityRepository;

class CaseManager
{
    /**
     * @var CaseEntityRepository
     */
    private $caseRepository;

    /**
     * @var WorkflowService
     */
    private $workflowService;

    public function __construct(CaseEntityRepository $caseRepository, WorkflowService $workflowService)
    {
        $this->caseRepository = $caseRepository;
        $this->workflowService = $workflowService;
    }

    public function generateCaseNumber(Municipality $municipality): string
    {
        // Find latest case in respective municipality
        $case = $this->caseRepository->findLatestCaseByMunicipality($municipality);

        // Get current year
        $date = new \DateTime();
        $currentYear = $date->format('Y');

        // Check if first case
        if (null === $case) {
            return $currentYear.'-0001';
        }

        // Get the year latest case is from
        $latestCaseYear = $case->getCreatedAt()->format('Y');

        // Check if we have entered a new year
        if ($currentYear !== $latestCaseYear) {
            return $currentYear.'-0001';
        }

        return $latestCaseYear.'-'.$this->getIncrementedCaseCounter($case->getCaseNumber());
    }

    public function getIncrementedCaseCounter(string $caseNumber): string
    {
        // Find dash, get counter and increment it
        $positionOfDash = strpos($caseNumber, '-');
        $counter = (int) substr($caseNumber, $positionOfDash + 1);
        $incrementedCounter = $counter + 1;

        return str_pad(strval($incrementedCounter), 4, '0', STR_PAD_LEFT);
    }

    public function newCase(CaseEntity $caseEntity, Board $board): CaseEntity
    {
        $caseEntity->setCaseNumber(
            $this->generateCaseNumber($board->getMunicipality())
        );
        $caseEntity->setBoard($board);
        $caseEntity->setMunicipality($board->getMunicipality());

        $workflow = $this->workflowService->getWorkflowForCase($caseEntity);
        $workflow->getMarking($caseEntity);

        return $caseEntity;
    }
}
