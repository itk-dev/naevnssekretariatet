<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

use App\Exception\WebformSubmissionException;
use App\Repository\BoardRepository;
use App\Repository\ComplaintCategoryRepository;
use App\Repository\UserRepository;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaseSubmissionNormalizer implements SubmissionNormalizerInterface
{
    public function __construct(private BoardRepository $boardRepository, private DocumentUploader $documentUploader, private ComplaintCategoryRepository $complaintCategoryRepository, private string $selvbetjeningUserApiToken, private EntityManagerInterface $entityManager, private HttpClientInterface $httpClient, private UserRepository $userRepository)
    {
    }

    /**
     * Normalizes the properties shared by all case types.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $normalizedArray = [];

        // Board
        $containsBoardId = isset($submissionData['naevn']) && !empty($submissionData['naevn']);
        $containsMunicipalityId = isset($submissionData['kommune']) && !empty($submissionData['kommune']);

        if ($containsBoardId && $containsMunicipalityId) {
            $board = $this->boardRepository->findOneBy(['id' => $submissionData['naevn']]);

            if (!$board) {
                $message = sprintf('Could not find a board with id %s', $submissionData['naevn']);
                throw new WebformSubmissionException($message, 401);
            }

            $normalizedArray['board'] = $board;
        } else {
            $message = sprintf('Submission must contain both a board name and a municipality name.');
            throw new WebformSubmissionException($message);
        }

        // Bringer (name)
        $normalizedArray['bringer'] = isset($submissionData['indbringer_navn']) && !empty($submissionData['indbringer_navn'])
            ? $submissionData['indbringer_navn']
            : throw new WebformSubmissionException('Submission data does not contain a bringer name.')
        ;

        // Bringer ID type
        $normalizedArray['bringer_id_type'] = isset($submissionData['indbringer_id_type']) && !empty($submissionData['indbringer_id_type'])
            ? $submissionData['indbringer_id_type']
            : throw new WebformSubmissionException('Submission data does not contain a bringer ID type.')
        ;

        // Bringer ID identifier
        $containsCPRIdentifier = isset($submissionData['indbringer_id_cpr_nummer']) && !empty($submissionData['indbringer_id_cpr_nummer']);
        $containsCVRIdentifier = isset($submissionData['indbringer_id_cvr_nummer']) && !empty($submissionData['indbringer_id_cvr_nummer']);

        if ($containsCPRIdentifier && $containsCVRIdentifier) {
            $message = sprintf('Submission data may not contain both a CPR and CVR identifier.');
            throw new WebformSubmissionException($message);
        } elseif ($containsCPRIdentifier) {
            $normalizedArray['bringer_id_identifier'] = $submissionData['indbringer_id_cpr_nummer'];
            $normalizedArray['bringer_id_p_number'] = null;
        } elseif ($containsCVRIdentifier) {
            $normalizedArray['bringer_id_identifier'] = $submissionData['indbringer_id_cvr_nummer'];
            $normalizedArray['bringer_id_p_number'] = isset($submissionData['indbringer_id_p_nummer']) && !empty($submissionData['indbringer_id_p_nummer']) ? $submissionData['indbringer_id_p_nummer'] : null;
        } else {
            $message = sprintf('Submission data must contain a CPR or a CVR identifier.');
            throw new WebformSubmissionException($message);
        }

        // Bringer address
        $normalizedArray['bringer_address_street'] = isset($submissionData['indbringer_adresse_vej']) && !empty($submissionData['indbringer_adresse_vej'])
            ? $submissionData['indbringer_adresse_vej']
            : throw new WebformSubmissionException('Submission data does not contain a bringer address street.')
        ;

        $normalizedArray['bringer_address_number'] = isset($submissionData['indbringer_adresse_nummer']) && !empty($submissionData['indbringer_adresse_nummer'])
            ? $submissionData['indbringer_adresse_nummer']
            : throw new WebformSubmissionException('Submission data does not contain a bringer address number.')
        ;

        $normalizedArray['bringer_address_floor'] = isset($submissionData['indbringer_adresse_etage']) && !empty($submissionData['indbringer_adresse_etage'])
            ? $submissionData['indbringer_adresse_etage']
            : null
        ;

        $normalizedArray['bringer_address_side'] = isset($submissionData['indbringer_adresse_side']) && !empty($submissionData['indbringer_adresse_side'])
            ? $submissionData['indbringer_adresse_side']
            : null
        ;

        $normalizedArray['bringer_address_postal_code'] = isset($submissionData['indbringer_adresse_postnummer']) && !empty($submissionData['indbringer_adresse_postnummer'])
            ? (int) $submissionData['indbringer_adresse_postnummer']
            : throw new WebformSubmissionException('Submission data does not contain a bringer address postal code.')
        ;

        $normalizedArray['bringer_address_city'] = isset($submissionData['indbringer_adresse_by']) && !empty($submissionData['indbringer_adresse_by'])
            ? (int) $submissionData['indbringer_adresse_by']
            : throw new WebformSubmissionException('Submission data does not contain a bringer address city.')
        ;

        $normalizedArray['bringer_address_extra_information'] = isset($submissionData['indbringer_adresse_ekstra_adresse_information']) && !empty($submissionData['indbringer_adresse_ekstra_adresse_information'])
            ? $submissionData['indbringer_adresse_ekstra_adresse_information']
            : null
        ;

        // Complaint category
        if (isset($submissionData['klagetype']) && !empty($submissionData['klagetype'])) {
            $complaintCategory = $this->complaintCategoryRepository->findOneByNameAndBoard($submissionData['klagetype'], $board);

            if (!$complaintCategory) {
                $message = sprintf('Could not find a complaint category with name %s  and board id %s', $submissionData['klagetype'], $submissionData['naevn']);
                throw new WebformSubmissionException($message, 401);
            }

            $normalizedArray['complaint_category'] = $complaintCategory;
        } else {
            $message = sprintf('Submission data does not contain a complaint category.');
            throw new WebformSubmissionException($message);
        }

        // Documents
        if (isset($submissionData['dokumenter']) && !empty($submissionData['dokumenter'])) {
            $documents = [];
            foreach ($submissionData['dokumenter'] as $documentId) {
                try {
                    $options = [
                        'headers' => [
                            'api-key: '.$this->selvbetjeningUserApiToken,
                        ],
                    ];

                    // Get document information.
                    $response = $this->httpClient->request(
                        'GET',
                        $sender.'/entity/file/'.$documentId,
                        $options
                    );

                    $data = $response->toArray();
                    $documentUrl = $sender.$data['uri'][0]['url'];

                    // Get document.
                    $response = $this->httpClient->request(
                        'GET',
                        $documentUrl,
                        $options
                    );

                    // Create new document
                    // Example basename: before.png
                    // Example resulting newFileName: before-62b1aa95ef3cb.png
                    $documentName = preg_replace('/\..*/', '', basename($documentUrl));
                    $newFileName = $this->documentUploader->generateNewUniqueFileName($documentName);
                    $filePath = $this->documentUploader->getFullDirectory().'/'.$newFileName;
                    file_put_contents($filePath, $response->getContent());
                    $document = $this->documentUploader->createDocumentFromPath($newFileName, $documentName, 'OS2Forms');
                    $documents[] = $document;

                    // Persist now, flush when case is created.
                    $this->entityManager->persist($document);
                } catch (\Exception $e) {
                    throw new WebformSubmissionException($e->getMessage());
                }
            }

            $normalizedArray['documents'] = $documents;
        } else {
            $normalizedArray['documents'] = null;
        }

        return $normalizedArray;
    }
}
