<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

interface SubmissionNormalizerInterface
{
    /**
     * Method for normalizing submission data e.g. converts to int, DateTime etc. based on webformId.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array;
}
