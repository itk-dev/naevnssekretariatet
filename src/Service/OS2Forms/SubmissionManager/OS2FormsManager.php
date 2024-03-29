<?php

namespace App\Service\OS2Forms\SubmissionManager;

use App\Service\CaseManager;
use App\Service\HearingHelper;

class OS2FormsManager
{
    public function __construct(private CaseManager $caseManager, private HearingHelper $hearingHelper, private ResidentComplaintBoardCaseTypeManager $residentComplaintBoardCaseTypeManager, private RentComplaintBoardCaseTypeManager $rentComplaintBoardCaseTypeManager, private FenceComplaintBoardCaseTypeManager $fenceComplaintBoardCaseTypeManager, private HearingResponseManager $hearingResponseManager)
    {
    }

    /**
     * Forwards handling of OS2Form submission and provides a submission manager based on webform id.
     */
    public function handleOS2FormsSubmission(string $webformId, string $sender, array $submissionData)
    {
        // Beware that these needs updating if OS2Forms machine names are changed.
        match ($webformId) {
            'tvist1_beboerklagenaevnet_ny_sag' => $this->caseManager->handleOS2FormsCaseSubmission($sender, $submissionData, $this->residentComplaintBoardCaseTypeManager),
            'tvist1_huslejenaevnet_ny_sag' => $this->caseManager->handleOS2FormsCaseSubmission($sender, $submissionData, $this->rentComplaintBoardCaseTypeManager),
            'tvist1_hegnssynet_ny_sag' => $this->caseManager->handleOS2FormsCaseSubmission($sender, $submissionData, $this->fenceComplaintBoardCaseTypeManager),
            'tvist1_partshoeringssvar' => $this->hearingHelper->handleOS2FormsHearingSubmission($sender, $submissionData, $this->hearingResponseManager),
        };
    }
}
