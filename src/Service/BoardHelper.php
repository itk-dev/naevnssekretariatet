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

        $mainBoard = [];
        // Find cases for board
        foreach ($this->caseRepository->findReadyCasesWithoutActiveAgendaByBoard($board) as $case) {
            $mainBoard[$case->__toString()] = $case;
        }

        $caseChoices[$board->getName()] = $mainBoard;

        // Find cases for suitable boards
        $suitableBoards = $this->boardRepository->findDifferentSuitableBoards($board);

        foreach ($suitableBoards as $suitableBoard) {
            $suitableBoardCases = [];
            foreach ($this->caseRepository->findReadyCasesWithoutActiveAgendaByBoard($suitableBoard) as $case) {
                $suitableBoardCases[$case->__toString()] = $case;
            }

            $caseChoices[$suitableBoard->getName()] = $suitableBoardCases;
        }

        return $caseChoices;
    }
}
