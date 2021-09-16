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

        // Make them into arrays
        $complainants = [];
        $counterparties = [];

        foreach ($complainantRelations as $relation) {
            array_push($complainants, $relation->getParty());
        }

        foreach ($counterpartyRelations as $relation) {
            array_push($counterparties, $relation->getParty());
        }

        return ['template' => $templatePath, 'complainants' => $complainants, 'counterparties' => $counterparties];
    }
}
