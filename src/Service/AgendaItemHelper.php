<?php

namespace App\Service;

use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use App\Form\AgendaCaseItemEditType;
use App\Form\AgendaManuelItemType;

class AgendaItemHelper
{
    public function getFormType(AgendaItem $agendaItem): ?string
    {
        $formClass = null;
        switch (get_class($agendaItem)) {
            case AgendaCaseItem::class:
                $formClass = AgendaCaseItemEditType::class;
                break;
            case AgendaManuelItem::class:
                $formClass = AgendaManuelItemType::class;
                break;
        }

        return $formClass;
    }

    public function getTemplatePath(AgendaItem $agendaItem): ?string
    {
        $templatePath = null;
        switch (get_class($agendaItem)) {
            case AgendaCaseItem::class:
                $templatePath = 'agenda_case_item/edit.html.twig';
                break;
            case AgendaManuelItem::class:
                $templatePath = 'agenda_manuel_item/edit.html.twig';
                break;
        }

        return $templatePath;
    }

    public function setCorrectDateOnStartAndEnd($agendaItem, $agenda)
    {
        $year = $agenda->getDate()->format('Y');
        $month = $agenda->getDate()->format('m');
        $day = $agenda->getDate()->format('d');

        $agendaItem->getStartTime()->setDate($year, $month, $day);
        $agendaItem->getEndTime()->setDate($year, $month, $day);

        return $agendaItem;
    }
}
