<?php

namespace App\Service;

use App\Entity\Agenda;
use App\Entity\AgendaItem;

class AgendaHelper
{
    public function findAvailableBoardMembers(Agenda $agenda): array
    {
        $allBoardMembers = $agenda->getBoard()->getBoardMembers()->toArray();

        $currentBoardMembersOnAgenda = $agenda->getBoardmembers()->toArray();

        return array_diff($allBoardMembers, $currentBoardMembersOnAgenda);
    }

    public function sortAgendasAccordingToDate(array $agendas): array
    {
        if (!empty($agendas)) {
            usort($agendas, function (Agenda $agendaOne, Agenda $agendaTwo) {
                $agendaOneTimestamp = $agendaOne->getDate()->getTimestamp();
                $agendaTwoTimestamp = $agendaTwo->getDate()->getTimestamp();

                if ($agendaOneTimestamp === $agendaTwoTimestamp) {
                    return 0;
                }

                return $agendaOneTimestamp < $agendaTwoTimestamp ? -1 : 1;
            });
        }

        return $agendas;
    }

    public function sortAgendaItemsAccordingToStart(array $agendaItems): array
    {
        if (!empty($agendaItems)) {
            usort($agendaItems, function (AgendaItem $itemOne, AgendaItem $itemTwo) {
                $itemOneStartTime = $itemOne->getStartTime()->format('H:i');
                $itemTwoStartTime = $itemTwo->getStartTime()->format('H:i');

                if ($itemOneStartTime === $itemTwoStartTime) {
                    return 0;
                }

                return $itemOneStartTime < $itemTwoStartTime ? -1 : 1;
            });
        }

        return $agendaItems;
    }

    public function getFormOptionsForAgenda(Agenda $agenda): array
    {
        $options = [];

        $options['disabled'] = $agenda->isFinished();

        return $options;
    }
}
