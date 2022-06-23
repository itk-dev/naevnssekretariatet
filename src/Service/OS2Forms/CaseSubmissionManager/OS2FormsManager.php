<?php

namespace App\Service\OS2Forms\CaseSubmissionManager;

class OS2FormsManager
{
    public function __construct(private ResidentComplaintBoardCaseTypeManager $residentComplaintBoardCaseTypeManager)
    {
    }

    public function getOS2FormsCaseManagerFromWebformId(string $webformId)
    {
        // TODO: Needs updating when form ids are finalized.
        return match ($webformId) {
            'tvist1_opret_sag_test' => $this->residentComplaintBoardCaseTypeManager,
        };
    }
}
