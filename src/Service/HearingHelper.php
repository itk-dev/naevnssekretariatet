<?php

namespace App\Service;

use App\Entity\CaseEvent;
use App\Entity\HearingPostResponse;
use App\Service\OS2Forms\SubmissionManager\HearingResponseManager;
use Doctrine\ORM\EntityManagerInterface;

class HearingHelper
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function handleOS2FormsHearingSubmission(string $sender, array $submissionData, HearingResponseManager $manager)
    {
        $hearingResponse = $manager->createHearingResponseFromSubmissionData($sender, $submissionData);

        // TODO: handle everything on case
        $this->entityManager->persist($hearingResponse);

        // Create case event (sagshændelse).
        $this->createCaseEventFromHearingPostResponse($hearingResponse);

        $this->entityManager->flush();
    }

    private function createCaseEventFromHearingPostResponse(HearingPostResponse $hearingResponse)
    {
        $caseEvent = new CaseEvent();

        $case = $hearingResponse->getHearing()->getCaseEntity();
        $caseEvent->setCaseEntity($case);

        $caseEvent->setCategory(CaseEvent::CATEGORY_INCOMING);
        $caseEvent->setSubject(CaseEvent::SUBJECT_HEARING_CONTRADICTIONS_BRIEFING);
        $caseEvent->setCreatedAt($hearingResponse->getCreatedAt());

        $caseEvent->addDocument($hearingResponse->getDocument());

        foreach ($hearingResponse->getAttachments() as $attachment) {
            $caseEvent->addDocument($attachment->getDocument());
        }

        $this->entityManager->persist($caseEvent);
    }
}
