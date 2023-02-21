<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\CasePartyRelation;
use App\Entity\Party;
use App\Repository\CasePartyRelationRepository;
use App\Repository\PartyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartyHelper
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly PartyRepository $partyRepository, private readonly CasePartyRelationRepository $relationRepository, private readonly TranslatorInterface $translator)
    {
    }

    public function findPartyIndexChoices(CaseEntity $case): array
    {
        $partyChoices = $this->partyRepository->findBy(['isPartOfPartIndex' => true], ['name' => 'ASC']);

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

        if ($type === $this->getSortingPartyType($case)) {
            $case->setSortingParty($party->getName());
        } elseif ($type === $this->getSortingCounterpartyType($case)) {
            $case->setSortingCounterparty($party->getName());
        }

        $this->entityManager->flush();
    }

    public function setUpFormData(Party $party, CasePartyRelation $relation, FormInterface $form): FormInterface
    {
        $form->get('name')->setData($party->getName());
        $form->get('identification')->setData($party->getIdentification());
        $form->get('address')->setData($party->getAddress());
        $form->get('phoneNumber')->setData($party->getPhoneNumber());
        $form->get('isUnderAddressProtection')->setData($party->getIsUnderAddressProtection());
        $form->get('type')->setData($relation->getType());

        return $form;
    }

    public function handleEditParty(Party $party, CasePartyRelation $relation, $data)
    {
        $party->setName($data['name']);
        $party->setIdentification($data['identification']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setIsUnderAddressProtection($data['isUnderAddressProtection']);
        $relation->setType($data['type']);

        $this->entityManager->flush();
    }

    public function handleAddPartyForm(CaseEntity $case, Party $party, $data)
    {
        $party->setName($data['name']);
        $party->setIdentification($data['identification']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setIsUnderAddressProtection($data['isUnderAddressProtection']);

        // Do not add to part index from here
        $party->setIsPartOfPartIndex(false);

        $this->entityManager->persist($party);

        $relation = new CasePartyRelation();
        $relation->setCase($case);
        $relation->setParty($party);
        $relation->setType($data['type']);

        if ($data['type'] === $this->getSortingPartyType($case)) {
            $case->setSortingParty($data['name']);
        } elseif ($data['type'] === $this->getSortingCounterpartyType($case)) {
            $case->setSortingCounterparty($data['name']);
        }

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
        $partyTypes = $this->getPartyTypesByCase($case);
        $counterpartyTypes = $this->getCounterpartyTypesByCase($case);

        return array_merge($partyTypes, $counterpartyTypes);
    }

    public function getPartyTypesByCase(CaseEntity $case): array
    {
        $rawTypes = explode(
            PHP_EOL,
            $case->getBoard()->getPartyTypes()
        );

        return $this->getTrimmedTypes($rawTypes);
    }

    public function getCounterpartyTypesByCase(CaseEntity $case): array
    {
        $rawTypes = explode(
            PHP_EOL,
            $case->getBoard()->getCounterpartyTypes()
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
        $partyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getPartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $parties = array_map(
            fn($relation) => [
                'party' => $relation->getParty(),
                'type' => $relation->getType(),
            ], $partyRelations
        );

        $counterpartyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getCounterpartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $counterparties = array_map(
            fn($relation) => [
                'party' => $relation->getParty(),
                'type' => $relation->getType(),
            ], $counterpartyRelations
        );

        return ['parties' => $parties, 'counterparties' => $counterparties];
    }

    public function getSortingParty(CaseEntity $case): ?string
    {
        $type = $this->getSortingPartyType($case);

        $partyRelation = $this->relationRepository
            ->findOneBy([
                'case' => $case,
                'type' => $type,
                'softDeleted' => false,
            ])
        ;

        return $partyRelation ? $partyRelation->getParty()->getName() : null;
    }

    public function getSortingCounterparty(CaseEntity $case): ?string
    {
        $type = $this->getSortingCounterpartyType($case);

        $counterpartRelation = $this->relationRepository
            ->findOneBy([
                'case' => $case,
                'type' => $type,
                'softDeleted' => false,
            ])
        ;

        return $counterpartRelation ? $counterpartRelation->getParty()->getName() : null;
    }

    public function updateSortingProperties(CaseEntity $case)
    {
        $sortingParty = $this->getSortingParty($case);
        if (null !== $sortingParty) {
            $case->setSortingParty($sortingParty);
        }
        $sortingCounterparty = $this->getSortingCounterparty($case);
        if (null !== $sortingCounterparty) {
            $case->setSortingCounterparty($sortingCounterparty);
        }
    }

    private function getSortingPartyType(CaseEntity $case)
    {
        $partyTypes = $this->getPartyTypesByCase($case);
        // Return first entry in array
        return reset($partyTypes);
    }

    private function getSortingCounterpartyType(CaseEntity $case)
    {
        $counterpartTypes = $this->getCounterpartyTypesByCase($case);
        // Return first entry in array
        return reset($counterpartTypes);
    }

    public function getRelevantPartiesForHearingPostByCase(CaseEntity $case): array
    {
        $partyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getPartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $parties = [];
        foreach ($partyRelations as $relation) {
            $parties[$relation->getParty()->getName().', '.$relation->getType()] = $relation->getParty();
        }

        // Sort alphabetically
        uasort($parties, static fn ($a, $b) => $a->getName() <=> $b->getName());

        $counterpartyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getCounterpartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $counterparties = [];
        foreach ($counterpartyRelations as $relation) {
            $counterparties[$relation->getParty()->getName().', '.$relation->getType()] = $relation->getParty();
        }

        // Sort alphabetically
        uasort($counterparties, static fn ($a, $b) => $a->getName() <=> $b->getName());

        return [$this->translator->trans('Parties', [], 'case') => $parties, $this->translator->trans('Counterparties', [], 'case') => $counterparties];
    }
}
