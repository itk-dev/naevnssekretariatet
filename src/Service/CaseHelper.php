<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\ResidentComplaintBoardCase;
use App\Repository\CasePartyRelationRepository;

class CaseHelper
{
    /**
     * @var CasePartyRelationRepository
     */
    private $relationRepository;

    public function __construct(CasePartyRelationRepository $relationRepository)
    {
        $this->relationRepository = $relationRepository;
    }

    /**
     * Returns array containing relevant template path and party arrays.
     *
     * @return array[]
     */
    public function getRelevantTemplateAndPartiesByCase(CaseEntity $case): array
    {
        switch (get_class($case)) {
            case ResidentComplaintBoardCase::class:
                // Get relations from both sides
                $complainantRelations = $this->relationRepository
                    ->findBy(['case' => $case, 'type' => ['Tenant', 'Representative'], 'softDeleted' => false]);
                $counterpartyRelations = $this->relationRepository
                    ->findBy(['case' => $case, 'type' => ['Landlord', 'Administrator'], 'softDeleted' => false]);
                $templatePath = 'case/show.html.twig';
                break;
        }

        $complainants = array_map(function ($relation) { return $relation->getParty(); }, $complainantRelations);
        $counterparties = array_map(function ($relation) { return $relation->getParty(); }, $counterpartyRelations);

        return ['template' => $templatePath, 'complainants' => $complainants, 'counterparties' => $counterparties];
    }

    /**
     * Takes as input array of cases and removes cases that are assigned active agenda.
     *
     * @return array[]
     */
    public function removeCasesWithActiveAgenda(array $cases): array
    {
        $casesWithoutActiveAgenda = [];
        foreach ($cases as $case) {
            if (!$this->hasActiveAgenda($case)) {
                array_push($casesWithoutActiveAgenda, $case);
            }
        }

        return $casesWithoutActiveAgenda;
    }

    /**
     * Checks if case has active agenda.
     */
    public function hasActiveAgenda(CaseEntity $case): bool
    {
        $agendaCaseItems = $case->getAgendaCaseItems();

        foreach ($agendaCaseItems as $agendaCaseItem) {
            $agenda = $agendaCaseItem->getAgenda();
            if (AgendaStatus::OPEN === $agenda->getStatus() || AgendaStatus::FULL === $agenda->getStatus()) {
                return true;
            }
        }

        return false;
    }
}
