<?php

namespace App\Service\OS2Forms\SubmissionNormalizers;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Exception\WebformSubmissionException;
use Doctrine\ORM\EntityManagerInterface;

class CaseSubmissionNormalizer extends AbstractSubmissionNormalizer
{
    protected function getConfig(): array
    {
        return [
            'board' => [
                'os2forms_key' => 'naevn',
                'required' => true,
                'type' => 'entity',
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    $boardId = $submissionData[$spec['os2forms_key']] ?? null;
                    $board = $entityManager->getRepository(Board::class)->findOneBy(['id' => $boardId]);
                    if (null === $board) {
                        throw new WebformSubmissionException(sprintf('Cannot get board %s', $boardId));
                    }

                    return $board;
                },
                'error_message' => 'Submission data does not contain a board.',
            ],
            'bringer' => [
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    if (isset($submissionData['indbringer_navn_cpr']) && !empty($submissionData['indbringer_navn_cpr'])) {
                        return $submissionData['indbringer_navn_cpr'];
                    }

                    if (isset($submissionData['indbringer_navn_cvr']) && !empty($submissionData['indbringer_navn_cvr'])) {
                        return $submissionData['indbringer_navn_cvr'];
                    }

                    return null;
                },
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer name.',
            ],
            'bringer_id_type' => [
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    if (isset($submissionData['indbringer_id_cpr_nummer']) && !empty($submissionData['indbringer_id_cpr_nummer'])) {
                        return 'CPR';
                    }

                    if (isset($submissionData['indbringer_id_cvr_nummer']) && !empty($submissionData['indbringer_id_cvr_nummer'])) {
                        return 'CVR';
                    }

                    return null;
                },
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data must contain an id identifier to be used for detecting id type',
            ],
            'bringer_id_identifier' => [
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    if (isset($submissionData['indbringer_id_cpr_nummer']) && !empty($submissionData['indbringer_id_cpr_nummer'])) {
                        return $submissionData['indbringer_id_cpr_nummer'];
                    }

                    if (isset($submissionData['indbringer_id_cvr_nummer']) && !empty($submissionData['indbringer_id_cvr_nummer'])) {
                        return $submissionData['indbringer_id_cvr_nummer'];
                    }

                    return null;
                },
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data must contain an id identifier',
            ],
            'bringer_id_p_number' => [
                'os2forms_key' => 'indbringer_id_p_nummer',
                'type' => 'string',
            ],
            'bringer_address_street' => [
                'os2forms_key' => 'indbringer_adresse_vej',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address street.',
            ],
            'bringer_address_number' => [
                'os2forms_key' => 'indbringer_adresse_nummer',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address number.',
            ],
            'bringer_address_floor' => [
                'os2forms_key' => 'indbringer_adresse_etage',
                'type' => 'string',
            ],
            'bringer_address_side' => [
                'os2forms_key' => 'indbringer_adresse_side',
                'type' => 'string',
            ],
            'bringer_address_postal_code' => [
                'os2forms_key' => 'indbringer_adresse_postnummer',
                'required' => true,
                'type' => 'int',
                'error_message' => 'Submission data does not contain a bringer address postal code.',
            ],
            'bringer_address_city' => [
                'os2forms_key' => 'indbringer_adresse_by',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address city.',
            ],
            'bringer_address_extra_information' => [
                'os2forms_key' => 'indbringer_adresse_ekstra_adresse_information',
                'type' => 'string',
            ],
            'complaint_category' => [
                'os2forms_key' => 'klagetype',
                'required' => true,
                'type' => 'entity',
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    $boardId = $submissionData['naevn'] ?? null;
                    $board = $entityManager->getRepository(Board::class)->findOneBy(['id' => $boardId]);
                    if (null === $board) {
                        throw new WebformSubmissionException(sprintf('Cannot get board %s', $boardId));
                    }

                    $complaintCategoryName = $submissionData[$spec['os2forms_key']] ?? null;
                    $complaintCategory = $entityManager->getRepository(ComplaintCategory::class)->findOneByNameAndBoard($complaintCategoryName, $board);

                    if (null === $complaintCategory) {
                        throw new WebformSubmissionException(sprintf('Cannot get complaint category %s for board %s', $complaintCategoryName, $board->getName()));
                    }

                    return $complaintCategory;
                },
                'error_message' => 'Submission data does not contain a complaint category.',
            ],
            'complaint_category_extra_information' => [
                'os2forms_key' => 'ekstra_klagetype_information',
                'type' => 'string',
            ],
            'documents' => [
                'os2forms_key' => 'dokumenter',
                'type' => 'documents',
            ],
        ];
    }
}
