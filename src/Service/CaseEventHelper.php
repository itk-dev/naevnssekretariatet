<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\CaseEventPartyRelation;
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
            ->setReceivedAt(new \DateTime('now'))
            ->setCreatedBy($this->security->getUser())
            ->setDigitalPost($digitalPost)
        ;

        $this->createCaseEventRelations($caseEvent, CaseEventPartyRelation::TYPE_RECIPIENT, $recipients);

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    public function createManualCaseEvent(CaseEntity $case, string $subject, string $note, array $senders, array $recipients, \DateTime $receivedAt)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->setCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_NOTE)
            ->setSubject($subject)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
            ->setNoteContent($note)
        ;

        if (!empty($senders)) {
            $this->createCaseEventRelations($caseEvent, CaseEventPartyRelation::TYPE_SENDER, $senders);
        }

        if (!empty($recipients)) {
            $this->createCaseEventRelations($caseEvent, CaseEventPartyRelation::TYPE_RECIPIENT, $recipients);
        }

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    public function createDocumentCaseEvent(CaseEntity $case, Party $sender, array $documents, \DateTime $receivedAt)
    {
        $caseEvent = new CaseEvent();

        $caseEvent
            ->setCaseEntity($case)
            ->setCategory(CaseEvent::CATEGORY_INCOMING)
            ->setSubject(CaseEvent::SUBJECT_HEARING_CONTRADICTIONS_BRIEFING)
            ->setReceivedAt($receivedAt)
            ->setCreatedBy($this->security->getUser())
        ;

        foreach ($documents as $document) {
            $caseEvent->addDocument($document);
        }

        $this->createCaseEventRelations($caseEvent, CaseEventPartyRelation::TYPE_SENDER, [$sender]);

        $this->entityManager->persist($caseEvent);
        $this->entityManager->flush();
    }

    private function createCaseEventRelations(CaseEvent $caseEvent, string $type, array $parties)
    {
        foreach ($parties as $party) {
            $caseEventPartyRelation = new CaseEventPartyRelation();

            $caseEventPartyRelation
                ->setParty($party)
                ->setCaseEvent($caseEvent)
                ->setType($type)
            ;

            $this->entityManager->persist($caseEventPartyRelation);
        }
    }
}
