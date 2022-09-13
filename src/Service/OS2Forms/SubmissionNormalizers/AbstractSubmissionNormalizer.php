<?php

namespace App\Service\OS2Forms\SubmissionNormalizers;

use App\Exception\WebformSubmissionException;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractSubmissionNormalizer implements SubmissionNormalizerInterface
{
    public function __construct(private DocumentUploader $documentUploader, private string $selvbetjeningUserApiToken, private EntityManagerInterface $entityManager, private HttpClientInterface $httpClient)
    {
    }

    /**
     * Normalizes the submission data according to extending classes config.
     */
    public function normalizeSubmissionData(string $sender, array $submissionData): array
    {
        $config = $this->getConfig();

        return $this->handleConfig($sender, $config, $submissionData);
    }

    /**
     * Handles submission data according to extending classes config.
     */
    public function handleConfig(string $sender, array $config, array $submissionData): array
    {
        $normalizedArray = [];

        foreach ($config as $property => $spec) {
            $value = isset($spec['value_callback'])
                ? $spec['value_callback']($property, $spec, $submissionData, $normalizedArray, $this->entityManager)
                : $submissionData[$spec['os2forms_key']] ?? null
            ;

            if (isset($spec['type']) && null !== $value) {
                $type = $spec['type'];

                // If an element is left empty in a submission it is submitted as an empty string.
                if ('' === $value) {
                    $value = null;
                }

                // If value is null just continue
                if (null !== $value) {
                    if (in_array($type, ['string', 'int'])) {
                        settype($value, $type);
                    } elseif ('boolean' === $type) {
                        $value = match ($value) {
                            'Ja', 1 => true,
                            'Nej', 0 => false,
                            // If a boolean property is not answered it will be submitted as an empty string (since we use choice elements)
                            '' => null,
                            default => throw new WebformSubmissionException(sprintf('The property value %s cannot be transformed into bool. %s', $value, $property))
                        };
                    } elseif ('datetime' === $type) {
                        $value = new \DateTime($value, new \DateTimeZone($spec['time_zone'] ?? 'UTC'));
                    } elseif ('documents' === $type) {
                        $value = is_array($value) && !empty($value) ? $this->handleDocuments($spec['os2forms_key'], $submissionData) : null;
                    }
                }
            }

            if (isset($spec['allowed_values']) && !in_array($value, $spec['allowed_values'], true)) {
                $message = sprintf('Property %s value %s must be one of the following values: %s.', $property, $value, implode(', ', $spec['allowed_values']));
                throw new WebformSubmissionException($message);
            }

            $isRequired = $spec['required'] ?? false;
            if ($isRequired && null === $value) {
                // All required properties should have an 'error_message' configuration.
                throw new WebformSubmissionException($spec['error_message'] ?? sprintf('Something went wrong handling the %s property.', $property));
            }

            $normalizedArray[$property] = $value;
        }

        return $normalizedArray;
    }

    private function handleDocuments(string $key, array $submissionData): array
    {
        $options = [
            'headers' => [
                'api-key: '.$this->selvbetjeningUserApiToken,
            ],
        ];

        $documents = [];

        foreach ($submissionData[$key] as $documentId) {
            try {
                // @see https://github.com/itk-dev/os2forms_selvbetjening/tree/develop/web/modules/custom/os2forms_rest_api#example-file-modifications
                $documentUrl = $submissionData['linked'][$key][$documentId]['url'];

                // Get document.
                $response = $this->httpClient->request(
                    'GET',
                    $documentUrl,
                    $options
                );

                $newFilePath = sys_get_temp_dir().'/'.basename($documentUrl);

                try {
                    // Place contents from HTTP call
                    file_put_contents($newFilePath, $response->getContent());

                    // Create document
                    $document = $this->documentUploader->createDocumentFromPath($newFilePath, basename($documentUrl), 'OS2Forms');
                } finally {
                    if (file_exists($newFilePath)) {
                        unlink($newFilePath);
                    }
                }

                $documents[] = $document;

                // Persist now, flush when case is created.
                $this->entityManager->persist($document);
            } catch (\Exception $e) {
                throw new WebformSubmissionException($e->getMessage());
            }
        }

        return $documents;
    }

    /**
     * Gets configuration for extending class.
     */
    abstract protected function getConfig(): array;
}
