<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

class RentBoardSubmissionNormalizer implements SubmissionNormalizerInterface
{
    /**
     * Normalizes the properties specific to RentBoardCase.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $normalizedArray = [];

        // @Todo: Lejemålstype

        return $normalizedArray;
    }
}
