<?php

namespace App\Service\OS2Forms\SubmissionNormalizers;

use App\Entity\CaseEntity;
use App\Exception\WebformSubmissionException;
use Doctrine\ORM\EntityManagerInterface;

class HearingResponseSubmissionNormalizer extends AbstractSubmissionNormalizer
{
    protected function getConfig(): array
    {
        return [
            'case' => [
                'os2forms_key' => 'sags_id',
                'required' => true,
                'type' => 'entity',
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    $caseId = $submissionData[$spec['os2forms_key']] ?? null;
                    $case = $entityManager->getRepository(CaseEntity::class)->findOneBy(['id' => $caseId]);
                    if (null === $case) {
                        throw new WebformSubmissionException(sprintf('Cannot get case %s', $caseId));
                    }

                    return $case;
                },
                'error_message' => 'Submission data does not contain a case.',
            ],
            'name' => [
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    if (isset($submissionData['navn_cpr']) && !empty($submissionData['navn_cpr'])) {
                        return $submissionData['navn_cpr'];
                    }

                    if (isset($submissionData['navn_cvr']) && !empty($submissionData['navn_cvr'])) {
                        return $submissionData['navn_cvr'];
                    }

                    return null;
                },
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data must contain a name',
            ],
            'identifier' => [
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    if (isset($submissionData['identifier_cpr']) && !empty($submissionData['identifier_cpr'])) {
                        return $submissionData['identifier_cpr'];
                    }

                    if (isset($submissionData['identifier_cvr']) && !empty($submissionData['identifier_cvr'])) {
                        return $submissionData['identifier_cvr'];
                    }

                    return null;
                },
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data must contain an identifier',
            ],
            'response' => [
                'os2forms_key' => 'svar',
                'type' => 'string',
            ],
            'documents' => [
                'os2forms_key' => 'dokumenter',
                'type' => 'documents',
            ],
        ];
    }
}
