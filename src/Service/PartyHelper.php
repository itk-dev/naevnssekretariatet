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
    public function __construct(private EntityManagerInterface $entityManager, private PartyRepository $partyRepository, private CasePartyRelationRepository $relationRepository, private TranslatorInterface $translator)
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

        if ($type === $this->getSortingComplainantType($case)) {
            $case->setSortingComplainant($party->getName());
        } elseif ($type === $this->getSortingCounterpartyType($case)) {
            $case->setSortingCounterparty($party->getName());
        }

        $this->entityManager->flush();
    }

    public function setUpFormData(Party $party, CasePartyRelation $relation, FormInterface $form): FormInterface
    {
        $form->get('name')->setData($party->getName());
        $form->get('identifierType')->setData($party->getIdentifierType());
        $form->get('identifier')->setData($party->getIdentifier());
        $form->get('address')->setData($party->getAddress());
        $form->get('phoneNumber')->setData($party->getPhoneNumber());
        $form->get('journalNumber')->setData($party->getJournalNumber());
        $form->get('type')->setData($relation->getType());

        return $form;
    }

    public function handleEditParty(Party $party, CasePartyRelation $relation, $data)
    {
        $party->setName($data['name']);
        $party->setIdentifierType($data['identifierType']);
        $party->setIdentifier($data['identifier']);
        $party->setAddress($data['address']);
        $party->setPhoneNumber($data['phoneNumber']);
        $party->setJournalNumber($data['journalNumber']);
        $relation->setType($data['type']);

        $this->entityManager->flush();
    }

    public function handleAddPartyForm(CaseEntity $case, Party $party, $data)
    {
        $party->setName($data['name']);
        $party->setIdentifierType($data['identifierType']);
        $party->setIdentifier($data['identifier']);
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

        if ($data['type'] === $this->getSortingComplainantType($case)) {
            $case->setSortingComplainant($data['name']);
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
        $complainantTypes = $this->getComplainantTypesByCase($case);
        $counterpartyTypes = $this->getCounterpartyTypesByCase($case);

        return array_merge($complainantTypes, $counterpartyTypes);
    }

    public function getComplainantTypesByCase(CaseEntity $case): array
    {
        $rawTypes = explode(
            PHP_EOL,
            $case->getBoard()->getComplainantTypes()
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
        $complainantRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getComplainantTypesByCase($case),
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

        $counterpartyRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getCounterpartyTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $counterparties = array_map(
            function ($relation) {
                return [
                    'party' => $relation->getParty(),
                    'type' => $relation->getType(),
                ];
            }, $counterpartyRelations
        );

        return ['complainants' => $complainants, 'counterparties' => $counterparties];
    }

    public function getSortingComplainant(CaseEntity $case): ?string
    {
        $type = $this->getSortingComplainantType($case);

        $complainantRelation = $this->relationRepository
            ->findOneBy([
                'case' => $case,
                'type' => $type,
                'softDeleted' => false,
            ])
        ;

        return $complainantRelation ? $complainantRelation->getParty()->getName() : null;
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
        $sortingComplainant = $this->getSortingComplainant($case);
        if (null !== $sortingComplainant) {
            $case->setSortingComplainant($sortingComplainant);
        }
        $sortingCounterparty = $this->getSortingCounterparty($case);
        if (null !== $sortingCounterparty) {
            $case->setSortingCounterparty($sortingCounterparty);
        }
    }

    private function getSortingComplainantType(CaseEntity $case)
    {
        $complainantTypes = $this->getComplainantTypesByCase($case);
        // Return first entry in array
        return reset($complainantTypes);
    }

    private function getSortingCounterpartyType(CaseEntity $case)
    {
        $counterpartTypes = $this->getCounterpartyTypesByCase($case);
        // Return first entry in array
        return reset($counterpartTypes);
    }

    public function getRelevantPartiesForHearingPostByCase(CaseEntity $case): array
    {
        $complainantRelations = $this->relationRepository
            ->findBy([
                'case' => $case,
                'type' => $this->getComplainantTypesByCase($case),
                'softDeleted' => false,
            ])
        ;

        $complainants = [];
        foreach ($complainantRelations as $relation) {
            $complainants[$relation->getParty()->getName().', '.$relation->getType()] = $relation->getParty();
        }

        // Sort alphabetically
        uasort($complainants, static fn ($a, $b) => $a->getName() <=> $b->getName());

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

        return [$this->translator->trans('Complainants', [], 'case') => $complainants, $this->translator->trans('Counterparties', [], 'case') => $counterparties];
    }
}
