<?php

namespace App\Service;

use App\Entity\Agenda;
use App\Entity\AgendaItem;
use App\Entity\BoardRole;

class AgendaHelper
{
    public function findAvailableBoardMembers(Agenda $agenda): array
    {
        /** @var BoardRole[] $allBoardRoles */
        $allBoardRoles = $agenda->getBoard()->getBoardRoles()->toArray();

        $allBoardMembers = [];

        foreach ($allBoardRoles as $boardRole) {
            foreach ($boardRole->getBoardMembers() as $boardMember) {
                array_push($allBoardMembers, $boardMember);
            }
        }

        $currentBoardMembersOnAgenda = $agenda->getBoardmembers()->toArray();

        return array_diff($allBoardMembers, $currentBoardMembersOnAgenda);
    }

    public function sortAgendaItemsAccordingToStart(array $agendaItems): array
    {
        if (!empty($agendaItems)) {
            usort($agendaItems, function (AgendaItem $a, AgendaItem $b) {
                $ad = $a->getStartTime()->format('H:i');
                $bd = $b->getStartTime()->format('H:i');

                if ($ad === $bd) {
                    return 0;
                }

                return $ad < $bd ? -1 : 1;
            });
        }

        return $agendaItems;
    }
}
