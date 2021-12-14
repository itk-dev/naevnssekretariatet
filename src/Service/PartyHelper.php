<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\Party;
use App\Repository\CasePartyRelationRepository;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class PartyHelper
{
    /**
     * @var PartyRepository
     */
    private $partyRepository;
    /**
     * @var CasePartyRelationRepository
     */
    private $relationRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, PartyRepository $partyRepository, CasePartyRelationRepository $relationRepository)
    {
        $this->entityManager = $entityManager;
        $this->partyRepository = $partyRepository;
        $this->relationRepository = $relationRepository;
    }

    public function findPartyIndexChoices(CaseEntity $case): array
    {
        $partyChoices = $this->partyRepository->findBy(['isPartOfPartIndex' => true]);

        $alreadyAddedNonDeletedRelations = $this->relationRepository->findBy(['case' => $case, 'softDeleted' => false]);

        $alreadyAddedParties = [];
        foreach ($alreadyAddedNonDeletedRelations as $alreadyAddedRelation) {
            array_push($alreadyAddedParties, $alreadyAddedRelation->getParty());
        }

        return array_diff($partyChoices, $alreadyAddedParties);
    }

    public function handleAddPartyFromIndex(CaseEntity $case, $data)
    {
        $party = $data['partyToAdd'];
        $type = $data['type'];

        // Check if this party has been added previously and is soft deleted
        $existingRelation = $this->relationRepository->findOneBy(['case' => $case, 'party' => $party]);

        if (null === $existingRelation) {
            $relation = new CasePartyRelation();
            $relation->setCase($case);
            $relation->setParty($party);
            $relation->setType($type);

            $this->entityManager->persist($relation);
        } else {
            $existingRelation->setType($type);
            $existingRelation->setSoftDeleted(false);
            $existingRelation->setSoftDeletedAt(null);
        }

        $this->entityManager->flush();
    }

    public function setUpFormData(Party $party, CasePartyRelation $relation, FormInterface $form): FormInterface
    {
        $form->get('name')->setData($party->getName());
        $form->get('cpr')->setData($party->getCpr());
        $form->get('address')->setData($party->getAddress());
        $form->get('phoneNumber')->setData($party->getPhoneNumber());
        $form->get('journalNumber')->setData($party->getJournalNumber());
        $form->get('type')->setData($relation->getType());

        return $form;
    }

    public function handleEditParty(Party $party, CasePartyRelation $relation, $data)
    {
        $party->setName($data['name']);
        $party->setCpr($data['cpr']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setJournalNumber($data['journalNumber']);
        $relation->setType($data['type']);

        $this->entityManager->flush();
    }

    public function handleAddPartyForm(CaseEntity $case, Party $party, $data)
    {
        $party->setName($data['name']);
        $party->setCpr($data['cpr']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setJournalNumber($data['journalNumber']);

        // Do not add to part index from here
        $party->setIsPartOfPartIndex(false);

        $this->entityManager->persist($party);

        $relation = new CasePartyRelation();
        $relation->setCase($case);
        $relation->setParty($party);
        $relation->setType($data['type']);

        $this->entityManager->persist($relation);

        $this->entityManager->flush();
    }

    public function handleDeleteParty(CaseEntity $case, Party $party)
    {
        $relation = $this->relationRepository->findOneBy(['case' => $case, 'party' => $party]);
        $relation->setSoftDeleted(true);
        $dateTime = new \DateTime('NOW');
        $relation->setSoftDeletedAt($dateTime);

        $this->entityManager->flush();
    }

    public function getAllPartyTypes(CaseEntity $case): array
    {
        $complainantPartyTypes = $this->getComplainantPartyTypesByCase($case);
        $counterPartyTypes = $this->getCounterPartyTypesByCase($case);

        return array_merge($complainantPartyTypes, $counterPartyTypes);
    }

    public function getComplainantPartyTypesByCase(CaseEntity $case): array
    {
        $rawTypes = explode(
            PHP_EOL,
            $case->getBoard()->getComplainantPartyTypes()
        );

        return $this->getTrimmedTypes($rawTypes);
    }

    public function getCounterPartyTypesByCase(CaseEntity $case): array
    {
        $rawTypes = explode(
            PHP_EOL,
            $case->getBoard()->getCounterPartyTypes()
        );

        return $this->getTrimmedTypes($rawTypes);
    }

    public function getTrimmedTypes(array $rawTypes): array
    {
        $trimmedTypes = [];
        foreach ($rawTypes as $rawType) {
            $trimmedRawType = trim($rawType);
            $trimmedTypes[$trimmedRawType] = $trimmedRawType;
        }

        return $trimmedTypes;
    }

    /**
     * Returns array containing relevant party arrays.
     *
     * @return array[]
     */
    public function getRelevantPartiesByCase(CaseEntity $case): array
    {
        $complainantRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getComplainantPartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $complainants = array_map(
            function ($relation) {
                return [
                    'party' => $relation->getParty(),
                    'type' => $relation->getType(),
                ];
            }, $complainantRelations
        );

        $counterPartyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getCounterPartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $counterparties = array_map(
            function ($relation) {
                return [
                    'party' => $relation->getParty(),
                    'type' => $relation->getType(),
                ];
            }, $counterPartyRelations
        );

        return ['complainants' => $complainants, 'counterparties' => $counterparties];
    }

    public function getSortingRelevantComplainant(CaseEntity $case): string
    {
        $complainantTypes = $this->getComplainantPartyTypesByCase($case);
        // Gets first entry in array
        $relevantType = reset($complainantTypes);

        $complainantRelation = $this->relationRepository
            ->findOneBy([
                'case' => $case,
                'type' => $relevantType,
                'softDeleted' => false,
            ])
        ;

        return $complainantRelation ? $complainantRelation->getParty()->getName() : ' ';
    }

    public function getSortingRelevantCounterpart(CaseEntity $case): string
    {
        $counterpartTypes = $this->getCounterPartyTypesByCase($case);
        // Gets first entry in array
        $relevantType = reset($counterpartTypes);

        $counterpartRelation = $this->relationRepository
            ->findOneBy([
                'case' => $case,
                'type' => $relevantType,
                'softDeleted' => false,
            ])
        ;

        return $counterpartRelation ? $counterpartRelation->getParty()->getName() : ' ';
    }
}
