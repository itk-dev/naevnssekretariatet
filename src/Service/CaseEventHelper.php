<?php

namespace App\Service;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\DigitalPost;
use App\Entity\Party;
use App\Repository\CaseEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CaseEventHelper
{
    public function __construct(private EntityManagerInterface $entityManager, private Security $security, private CaseEntityRepository $caseRepository)
    {
    }

    public function createDigitalPostCaseEvent(CaseEntity $case, DigitalPost $digitalPost, array $recipients)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->addCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_OUTGOING)
            ->setSubject(CaseEvent::SUBJECT_HEARING_CONTRADICTIONS_BRIEFING)
            ->setReceivedAt(new \DateTimeImmutable())
            ->setCreatedBy($this->security->getUser())
            ->setDigitalPost($digitalPost)
            ->setRecipients(array_map(static fn (Party $party) => $party->getName(), $recipients))
        ;

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    public function createManualCaseEvent(CaseEntity $case, string $subject, string $note, array $partySenders, ?string $additionalSenders, array $partyRecipients, ?string $additionalRecipients, \DateTimeInterface $receivedAt)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->addCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_NOTE)
            ->setSubject($subject)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
            ->setNoteContent($note)
            ->setSenders($this->computeCaseEventSenderOrRecipient($partySenders, $this->getLines($additionalSenders)))
            ->setRecipients($this->computeCaseEventSenderOrRecipient($partyRecipients, $this->getLines($additionalRecipients)))
        ;

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    public function createDocumentCaseEvent(CaseEntity $case, string $subject, array $partySenders, ?string $additionalSenders, array $partyRecipients, ?string $additionalRecipients, array $documents, \DateTimeInterface $receivedAt = new \DateTimeImmutable())
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->addCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_INCOMING)
            ->setSubject($subject)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
            ->setSenders($this->computeCaseEventSenderOrRecipient($partySenders, $this->getLines($additionalSenders)))
            ->setRecipients($this->computeCaseEventSenderOrRecipient($partyRecipients, $this->getLines($additionalRecipients)))
        ;

        foreach ($documents as $document) {
            $caseEvent->addDocument($document);
        }

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    private function computeCaseEventSenderOrRecipient(array $parties, array $additionalParties): array
    {
        $names = array_map(static fn (Party $party) => $party->getName(), $parties);

        return [...$names, ...$additionalParties];
    }

    private function getLines(?string $additionalParties): array
    {
        return array_filter(array_map('trim', explode(PHP_EOL, $additionalParties)));
    }

    public function handleCopyCaseEventForm(array $cases, CaseEvent $caseEvent)
    {
        foreach ($cases as $case) {
            $caseEvent->addCaseEntity($case);

            foreach ($caseEvent->getDocuments() as $document) {
                $relation = new CaseDocumentRelation();
                $relation->setCase($case);
                $relation->setDocument($document);
                $this->entityManager->persist($relation);
            }
        }

        $this->entityManager->flush();
    }

    public function findSuitableCasesForCopy(CaseEntity $case, CaseEvent $caseEvent, string $endStatus): array
    {
        $cases = $this->caseRepository->findNonFinishedCasesInSameBoard($case, $endStatus);

        return array_diff($cases, $caseEvent->getCaseEntities()->toArray());
    }
}
