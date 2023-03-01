<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\DigitalPost;
use App\Entity\Party;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CaseEventHelper
{
    public function __construct(private EntityManagerInterface $entityManager, private Security $security)
    {
    }

    public function createDigitalPostCaseEvent(CaseEntity $case, DigitalPost $digitalPost, array $recipients)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->setCaseEntity($case)
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

    public function createManualCaseEvent(CaseEntity $case, string $subject, string $note, array $partySenders, ?string $manualSenders, array $partyRecipients, ?string $manualRecipients, \DateTimeInterface $receivedAt)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->setCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_NOTE)
            ->setSubject($subject)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
            ->setNoteContent($note)
            ->setSenders($this->computeCaseEventSenderOrRecipient($partySenders, $manualSenders))
            ->setRecipients($this->computeCaseEventSenderOrRecipient($partyRecipients, $manualRecipients))
        ;

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    public function createDocumentCaseEvent(CaseEntity $case, Party $sender, array $documents, \DateTimeInterface $receivedAt = new \DateTimeImmutable())
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->setCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_INCOMING)
            ->setSubject(CaseEvent::SUBJECT_HEARING_CONTRADICTIONS_BRIEFING)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
            ->setSenders([$sender->getName()])
        ;

        foreach ($documents as $document) {
            $caseEvent->addDocument($document);
        }

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    private function computeCaseEventSenderOrRecipient(array $parties, ?string $suffix): array
    {
        $names = array_map(static fn (Party $party) => $party->getName(), $parties);

        if (null !== $suffix) {
            $names[] = $suffix;
        }

        return $names;
    }
}
