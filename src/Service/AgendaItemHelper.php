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
        $formClass = match ($agendaItem::class) {
            AgendaCaseItem::class => AgendaCaseItemEditType::class,
            AgendaManuelItem::class => AgendaManuelItemType::class,
            default => $formClass,
        };

        return $formClass;
    }

    public function getTemplatePath(AgendaItem $agendaItem): ?string
    {
        $templatePath = null;
        $templatePath = match ($agendaItem::class) {
            AgendaCaseItem::class => 'agenda_case_item/edit.html.twig',
            AgendaManuelItem::class => 'agenda_manuel_item/edit.html.twig',
            default => $templatePath,
        };

        return $templatePath;
    }
}
