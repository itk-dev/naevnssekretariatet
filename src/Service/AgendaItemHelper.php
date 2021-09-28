<?php

namespace App\Service;

use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use App\Form\AgendaCaseItemType;
use App\Form\AgendaManuelItemType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Form\FormInterface;

class AgendaItemHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handleCreateAgendaItemForm(FormInterface $form): AgendaItem
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

    public function handleEditAgendaItemForm(AgendaItem $agendaItem, FormInterface $form)
    {
        switch (get_class($agendaItem)){
            case AgendaCaseItem::class:
                $this->handleEditCaseItem($agendaItem, $form);
                break;
            case AgendaManuelItem::class:
                $this->handleEditManuelItem($agendaItem, $form);
                break;
        }
    }

    private function handleEditCaseItem(AgendaItem $agendaItem, FormInterface $form)
    {
        $agendaData = $form->getData();

        $agendaItem->setStartTime($agendaData['startTime']);
        $agendaItem->setEndTime($agendaData['endTime']);
        $agendaItem->setMeetingPoint($agendaData['meetingPoint']);
        $agendaItem->setCaseEntity($agendaData['caseEntity']);
        $agendaItem->setInspection($agendaData['inspection']);

        $this->entityManager->flush();
    }

    private function handleEditManuelItem(AgendaItem $agendaItem, FormInterface $form)
    {
        $agendaData = $form->getData();

        $agendaItem->setStartTime($agendaData['startTime']);
        $agendaItem->setEndTime($agendaData['endTime']);
        $agendaItem->setMeetingPoint($agendaData['meetingPoint']);
        $agendaItem->setTitle($agendaData['title']);
        $agendaItem->setDescription($agendaData['description']);

        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    public function getFormType(AgendaItem $agendaItem): string
    {
        switch (get_class($agendaItem)){
            case AgendaCaseItem::class:
                return AgendaCaseItemType::class;
            case AgendaManuelItem::class:
                return AgendaManuelItemType::class;
            default:
                $message = 'Unexpected agenda item class provided';
                throw new Exception($message);
        }
    }
}
