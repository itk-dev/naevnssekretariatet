<?php

namespace App\Service;

use App\Entity\Agenda;

class AgendaHelper
{
    public function getFormOptionsForAgenda(Agenda $agenda): array
    {
        $options = [];

        $options['disabled'] = $agenda->isFinished();

        return $options;
    }
}
