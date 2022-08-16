<?php

namespace App\Service;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
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

//        assert($case instanceof CaseEntity);
//
//        if (is_array($documents)) {
//            foreach ($documents as $document) {
//                $caseDocumentRelation = new CaseDocumentRelation();
//
//                $caseDocumentRelation
//                    ->setCase($case)
//                    ->setDocument($document)
//                ;
//
//                $this->entityManager->persist($caseDocumentRelation);
//            }
//        }
//        //TODO: handle everything on case
        $this->entityManager->persist($hearingResponse);

        $this->entityManager->flush();
    }
}
