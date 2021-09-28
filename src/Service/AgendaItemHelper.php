<?php

namespace App\Service;

use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use App\Form\AgendaCaseItemType;
use App\Form\AgendaManuelItemType;

class AgendaItemHelper
{

    public function getFormType(AgendaItem $agendaItem): ?string
    {
        $formClass = null;
        switch (get_class($agendaItem)){
            case AgendaCaseItem::class:
                $formClass = AgendaCaseItemType::class;
                break;
            case AgendaManuelItem::class:
                $formClass = AgendaManuelItemType::class;
                break;
        }

        return $formClass;
    }
}