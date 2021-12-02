<?php

namespace App\Service;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Repository\CaseEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;

class CaseManager
{
    /**
     * @var CaseEntityRepository
     */
    private $caseRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LockFactory
     */
    private $lockFactory;
    /**
     * @var WorkflowService
     */
    private $workflowService;

    public function __construct(CaseEntityRepository $caseRepository, EntityManagerInterface $entityManager, LockFactory $lockFactory, WorkflowService $workflowService)
    {
        $this->caseRepository = $caseRepository;
        $this->entityManager = $entityManager;
        $this->lockFactory = $lockFactory;
        $this->workflowService = $workflowService;
    }

    public function generateCaseNumber(Municipality $municipality): string
    {
        $case = $this->caseRepository->findLatestCase();

        // Get current year
        $date = new \DateTime();
        $currentYear = $date->format('y');

        // Check if first case
        if (null === $case) {
            return $currentYear.'-000001';
        }

        // Get the year latest case is from
        $latestCaseYear = $case->getCreatedAt()->format('y');

        // Check if we have entered a new year
        if ($currentYear !== $latestCaseYear) {
            return $currentYear.'-000001';
        }

        return $latestCaseYear.'-'.$this->getIncrementedCaseCounter($case->getCaseNumber());
    }

    public function getIncrementedCaseCounter(string $caseNumber): string
    {
        // Find dash, get counter and increment it
        $positionOfDash = strpos($caseNumber, '-');
        $counter = (int) substr($caseNumber, $positionOfDash + 1);
        $incrementedCounter = $counter + 1;

        return str_pad(strval($incrementedCounter), 6, '0', STR_PAD_LEFT);
    }

    public function newCase(CaseEntity $caseEntity, Board $board): CaseEntity
    {
        $lock = $this->lockFactory->createLock('case-generation');

        $lock->acquire(true);

        $caseEntity->setCaseNumber(
            $this->generateCaseNumber($board->getMunicipality())
        );
        $caseEntity->setBoard($board);
        $caseEntity->setMunicipality($board->getMunicipality());

        $workflow = $this->workflowService->getWorkflowForCase($caseEntity);
        $workflow->getMarking($caseEntity);

        $this->entityManager->persist($caseEntity);
        $this->entityManager->flush();

        $lock->release();

        return $caseEntity;
    }
}
