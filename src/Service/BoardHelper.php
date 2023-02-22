<?php

namespace App\Service;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\FenceReviewCase;
use App\Entity\RentBoardCase;
use App\Entity\ResidentComplaintBoardCase;
use App\Exception\CaseClassException;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;

class BoardHelper
{
    public function __construct(private readonly BoardRepository $boardRepository, private readonly CaseEntityRepository $caseRepository)
    {
    }

    public function getStatusesByBoard(Board $board): array
    {
        return array_filter(array_map('trim', explode(PHP_EOL, $board->getStatuses())));
    }

    public function getCasesReadyForAgendaByBoardAndSuitableBoards(Board $board): array
    {
        $caseChoices = [];

        $caseChoices[$board->getName()] = $this->caseRepository->findReadyCasesWithoutActiveAgendaByBoard($board);

        // Find cases for suitable boards
        $suitableBoards = $this->boardRepository->findDifferentSuitableBoards($board);

        foreach ($suitableBoards as $suitableBoard) {
            $caseChoices[$suitableBoard->getName()] = $this->caseRepository->findReadyCasesWithoutActiveAgendaByBoard($suitableBoard);
        }

        return $caseChoices;
    }

    /**
     * @throws CaseClassException
     */
    public function createNewCase(Board $board): CaseEntity
    {
        $caseType = $board->getCaseFormType();
        try {
            $case = match ($caseType) {
                'FenceReviewCaseType' => new FenceReviewCase(),
                'RentBoardCaseType' => new RentBoardCase(),
                'ResidentComplaintBoardCaseType' => new ResidentComplaintBoardCase()
            };
        } catch (\UnhandledMatchError) {
            $message = sprintf('Unhandled case form type %s', $caseType);
            throw new CaseClassException($message);
        }

        return $case;
    }
}
