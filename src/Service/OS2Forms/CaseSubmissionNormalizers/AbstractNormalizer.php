<?php

namespace App\Service\OS2Forms\CaseSubmissionNormalizers;

use App\Exception\WebformSubmissionException;
use App\Repository\BoardRepository;
use App\Repository\ComplaintCategoryRepository;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractNormalizer
{
    public function __construct(private BoardRepository $boardRepository, private DocumentUploader $documentUploader, private ComplaintCategoryRepository $complaintCategoryRepository, private string $selvbetjeningUserApiToken, private EntityManagerInterface $entityManager, private HttpClientInterface $httpClient)
    {
    }

    public function handleConfig(string $sender, array $config, array $submissionData): array
    {
        $normalizedArray = [];

        foreach ($config as $property => $spec) {
            $value = is_callable($spec['os2FormsKey'])
                ? $submissionData[$spec['os2FormsKey']($submissionData)]
                : $submissionData[$spec['os2FormsKey']] ?? null
            ;

            if (isset($spec['type'])) {
                $type = $spec['type'];

                if (in_array($type, ['string', 'int'])) {
                    settype($value, $type);
                } elseif ('bool' === $type) {
                    $value = match ($value) {
                        'Ja' => true,
                        'Nej' => false,
                        default => throw new WebformSubmissionException(sprintf('The property value %s cannot be transformed into bool. Permitted values are Ja/Nej.', $value))
                    };
                } elseif ('DateTime' === $type) {
                    $value = new \DateTime($value);
                } elseif ('entity' === $type) {
                    // Handle entity type. Expects 'get_entity_callback' option.

                    if (!is_callable($spec['get_entity_callback'])) {
                        throw new WebformSubmissionException('If type is entity, configuration must contain a get_entity_callback.');
                    }

                    $entity = match ($property) {
                        // If adding an entity type to a normalizer config, it must also be added here.
                        'board' => $spec['get_entity_callback']($this->boardRepository, $value),
                        'complaint_category' => $spec['get_entity_callback']($this->complaintCategoryRepository, $value, $normalizedArray['board']),
                        default => null,
                    };

                    if (!$entity) {
                        $message = sprintf('Could not find entity for %s', $property);
                        throw new WebformSubmissionException($message);
                    } else {
                        $value = $entity;
                    }
                } elseif ('documents' === $type) {
                    $value = is_array($value) && !empty($value) ? $this->handleDocuments($sender, $value) : null;
                } else {
                    $message = sprintf('Unhandled type %s.', $type);
                    throw new WebformSubmissionException($message);
                }
            }

            if (isset($spec['allowed_values']) && !in_array($value, $spec['allowed_values'], true)) {
                $message = sprintf('Property %s value %s must be one of the following values: %s.', $property, $value, implode(', ', $spec['allowed_values']));
                throw new WebformSubmissionException($message);
            }

            $isRequired = $spec['required'] ?? false;
            if ($isRequired && empty($value)) {
                // All required properties should have an 'error_message' configuration.
                throw new WebformSubmissionException($spec['error_message'] ?? sprintf('Something went wrong handling the %s property.', $property));
            } elseif (!$isRequired && empty($value)) {
                $value = null;
            }

            $normalizedArray[$property] = $value;
        }

        return $normalizedArray;
    }

    private function handleDocuments(string $sender, array $documentIds): array
    {
        $documents = [];
        foreach ($documentIds as $documentId) {
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

                $newFilePath = sys_get_temp_dir().'/'.basename($documentUrl);

                // Place contents from HTTP call
                file_put_contents($newFilePath, $response->getContent());

                // Create document
                $document = $this->documentUploader->createDocumentFromPath($newFilePath, basename($documentUrl), 'OS2Forms');

                $documents[] = $document;

                // Persist now, flush when case is created.
                $this->entityManager->persist($document);
            } catch (\Exception $e) {
                throw new WebformSubmissionException($e->getMessage());
            }
        }

        return $documents;
    }
}
