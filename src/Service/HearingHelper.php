<?php

namespace App\Service;

use App\Service\OS2Forms\SubmissionManager\HearingResponseManager;
use Doctrine\ORM\EntityManagerInterface;

class HearingHelper
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function handleOS2FormsHearingSubmission(string $sender, array $submissionData, HearingResponseManager $manager)
    {
        $hearingResponse = $manager->createHearingResponseFromSubmissionData($sender, $submissionData);

        //TODO: handle everything on case
        $this->entityManager->persist($hearingResponse);

        $this->entityManager->flush();
    }
}
