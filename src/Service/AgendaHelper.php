<?php

namespace App\Service;

use App\Entity\Agenda;

class AgendaHelper
{
    public function findAvailableBoardMembers(Agenda $agenda): array
    {
        $allBoardMembers = $agenda->getBoard()->getBoardMembers()->toArray();

        $currentBoardMembersOnAgenda = $agenda->getBoardmembers()->toArray();

        return array_diff($allBoardMembers, $currentBoardMembersOnAgenda);
    }

    public function getFormOptionsForAgenda(Agenda $agenda): array
    {
        $options = [];

        $options['disabled'] = $agenda->isFinished();

        return $options;
    }

    public function setCorrectDatesOnStartAndEnd(Agenda $agenda): Agenda
    {
        $year = $agenda->getDate()->format('Y');
        $month = $agenda->getDate()->format('m');
        $day = $agenda->getDate()->format('d');

        $agenda->getStart()->setDate($year, $month, $day);
        $agenda->getEnd()->setDate($year, $month, $day);

        return $agenda;
    }
}
