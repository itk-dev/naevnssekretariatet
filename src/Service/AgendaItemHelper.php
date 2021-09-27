<?php

namespace App\Service;

use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use Symfony\Component\Form\FormInterface;

class AgendaItemHelper
{
    public function handleAgendaItemForm(FormInterface $form): AgendaItem
    {
        $type = $form->get('type')->getData();

        $agendaItem = null;
        switch ($type) {
            case 'Manuel item':
                $agendaItem = $this->handleManuelAgendaItem($form);
                break;
            case 'Case item':
                $agendaItem = $this->handleCaseAgendaItem($form);
                break;
        }

        return $agendaItem;
    }

    private function handleManuelAgendaItem(FormInterface $form): AgendaManuelItem
    {
        $agendaItem = new AgendaManuelItem();
        $agendaData = $form->get('agendaItem')->getData();

        $agendaItem->setStartTime($agendaData['startTime']);
        $agendaItem->setEndTime($agendaData['endTime']);
        $agendaItem->setMeetingPoint($agendaData['meetingPoint']);
        $agendaItem->setTitle($agendaData['title']);
        $agendaItem->setDescription($agendaData['description']);

        return $agendaItem;
    }

    private function handleCaseAgendaItem(FormInterface $form): AgendaCaseItem
    {
        $agendaItem = new AgendaCaseItem();
        $agendaData = $form->get('agendaItem')->getData();

        $agendaItem->setStartTime($agendaData['startTime']);
        $agendaItem->setEndTime($agendaData['endTime']);
        $agendaItem->setMeetingPoint($agendaData['meetingPoint']);
        $agendaItem->setCaseEntity($agendaData['caseEntity']);
        $agendaItem->setInspection($agendaData['inspection']);

        return $agendaItem;
    }
}
