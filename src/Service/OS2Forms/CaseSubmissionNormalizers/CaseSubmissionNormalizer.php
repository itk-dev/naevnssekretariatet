<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use App\Exception\WebformSubmissionException;
use Doctrine\ORM\EntityManagerInterface;

class CaseSubmissionNormalizer extends AbstractNormalizer implements SubmissionNormalizerInterface
{
    /**
     * Normalizes the properties shared by all case types.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $config = $this->getConfig();

        return $this->handleConfig($sender, $config, $submissionData);
    }

    protected function getConfig(): array
    {
        return [
            'board' => [
                'os2FormsKey' => 'naevn',
                'required' => true,
                'type' => 'entity',
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    $board = $entityManager->getRepository(Board::class)->findOneBy(['id' => $submissionData[$spec['os2FormsKey']] ?? null]);
                    if (null === $board) {
                        throw new WebformSubmissionException('blabla');
                    }

                    return $board;
                },
                'error_message' => 'Submission data does not contain a board.',
            ],
            'bringer' => [
                'os2FormsKey' => 'indbringer_navn',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer name.',
            ],
            'bringer_id_type' => [
                'os2FormsKey' => 'indbringer_id_type',
                'required' => true,
                'type' => 'string',
                'allowed_values' => [
                    'CPR',
                    'CVR',
                ],
                'error_message' => 'Submission data does not contain a bringer ID type.',
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
                'os2FormsKey' => 'indbringer_id_p_nummer',
                'type' => 'string',
            ],
            'bringer_address_street' => [
                'os2FormsKey' => 'indbringer_adresse_vej',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address street.',
            ],
            'bringer_address_number' => [
                'os2FormsKey' => 'indbringer_adresse_nummer',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address number.',
            ],
            'bringer_address_floor' => [
                'os2FormsKey' => 'indbringer_adresse_etage',
                'type' => 'string',
            ],
            'bringer_address_side' => [
                'os2FormsKey' => 'indbringer_adresse_side',
                'type' => 'string',
            ],
            'bringer_address_postal_code' => [
                'os2FormsKey' => 'indbringer_adresse_postnummer',
                'required' => true,
                'type' => 'int',
                'error_message' => 'Submission data does not contain a bringer address postal code.',
            ],
            'bringer_address_city' => [
                'os2FormsKey' => 'indbringer_adresse_by',
                'required' => true,
                'type' => 'string',
                'error_message' => 'Submission data does not contain a bringer address city.',
            ],
            'bringer_address_extra_information' => [
                'os2FormsKey' => 'indbringer_adresse_ekstra_adresse_information',
                'type' => 'string',
            ],
            'complaint_category' => [
                'os2FormsKey' => 'klagetype',
                'required' => true,
                'type' => 'entity',
                'value_callback' => function (string $property, array $spec, array $submissionData, array $normalizedData, EntityManagerInterface $entityManager) {
                    $board = $entityManager->getRepository(Board::class)->findOneBy(['id' => $submissionData['naevn'] ?? null]);
                    if (null === $board) {
                        throw new WebformSubmissionException('blabla '.$submissionData['naevn']);
                    }

                    $complaintCategory = $entityManager->getRepository(ComplaintCategory::class)->findOneByNameAndBoard($submissionData[$spec['os2FormsKey']] ?? null, $board);

                    if (null === $complaintCategory) {
                        throw new WebformSubmissionException('blabla');
                    }

                    return $complaintCategory;
                },
                'error_message' => 'Submission data does not contain a complaint category.',
            ],
            'complaint_category_extra_information' => [
                'os2FormsKey' => 'ekstra_klagetype_information',
                'type' => 'string',
            ],
            'documents' => [
                'os2FormsKey' => 'dokumenter',
                'type' => 'documents',
            ],
        ];
    }
}
