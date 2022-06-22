<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

class FenceBoardSubmissionNormalizer implements SubmissionNormalizerInterface
{
    /**
     * Normalizes the properties specific for FenceBoardCase.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $normalizedArray = [];

        // @Todo
        // Indbringer matr.nr.
        // Modpart, modpart id, modpart adresse, modpart matr.nr.
        // Forhold ønsket bedømt
        // Ønskede resultat

        return $normalizedArray;
    }
}
