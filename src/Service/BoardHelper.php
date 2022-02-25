<?php

namespace App\Service;

use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;

class BoardHelper
{
    public function __construct(private BoardRepository $boardRepository, private CaseEntityRepository $caseRepository)
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
}
