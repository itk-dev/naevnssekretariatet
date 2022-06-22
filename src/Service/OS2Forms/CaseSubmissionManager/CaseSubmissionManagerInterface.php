<?php

namespace App\Service\OS2Forms\CaseSubmissionManager;

use App\Entity\CaseEntity;

interface CaseSubmissionManagerInterface
{
    /**
     * Returns array of data normalizers needed.
     */
    public function getNormalizers(): array;

    /**
     * Creates case from OS2Forms submission data.
     */
    public function createCaseFromSubmissionData(string $sender, array $submissionData): array;

    /**
     * Creates case from normalized OS2Forms submission data.
     */
    public function createCaseFromNormalizedSubmissionData(array $normalizedData): CaseEntity;
}
