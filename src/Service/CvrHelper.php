<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Identification;
use App\Exception\CvrException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\Exception\SecretException;
use ItkDev\AzureKeyVault\Exception\TokenException;
use ItkDev\AzureKeyVault\KeyVault\VaultCertificate;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CvrHelper
{
    /**
     * The client.
     */
    private Client $guzzleClient;
    private array $serviceOptions;

    public function __construct(private CaseManager $caseManager, private EntityManagerInterface $entityManager, private PropertyAccessorInterface $propertyAccessor, private TranslatorInterface $translator, array $options)
    {
        $this->guzzleClient = new Client();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'azure_tenant_id_test',
                'azure_application_id_test',
                'azure_client_secret_test',
                'azure_key_vault_name_test',
                'azure_key_vault_secret_test',
                'azure_key_vault_secret_version_test',
            ],
            )
        ;
    }

    /**
     * @throws CvrException
     */
    public function lookupCvr(string $cvr)
    {
        try {
            $certificate = $this->getAbsolutePathToSecret(
                $this->serviceOptions['azure_tenant_id_test'],
                $this->serviceOptions['azure_application_id_test'],
                $this->serviceOptions['azure_client_secret_test'],
                $this->serviceOptions['azure_key_vault_name_test'],
                $this->serviceOptions['azure_key_vault_secret_test'],
                $this->serviceOptions['azure_key_vault_secret_version_test']
            );

            $apiUrl = 'https://test03-s5-certservices.datafordeler.dk/CVR/HentCVRData/1/rest/hentVirksomhedMedCVRNummer?pCVRNummer='.$cvr;

            $client = new Client();
            $res = $client->request('GET', $apiUrl, [
                'cert' => $certificate,
            ]);
        } catch (SecretException|TokenException|GuzzleException $e) {
            throw new CvrException($e->getMessage(), $e->getCode(), $e);
        }

        return json_decode((string) $res->getBody(), true);
    }

    /**
     * Get absolute path to secret.
     *
     * @throws TokenException
     * @throws SecretException
     */
    private function getAbsolutePathToSecret(
        string $tenantId,
        string $applicationId,
        string $clientSecret,
        string $keyVaultName,
        string $keyVaultSecret,
        string $keyVaultSecretVersion
    ): string {
        $httpClient = new GuzzleAdapter($this->guzzleClient);
        $requestFactory = new RequestFactory();

        $vaultToken = new VaultToken($httpClient, $requestFactory);

        $token = $vaultToken->getToken(
            $tenantId,
            $applicationId,
            $clientSecret
        );

        $vaultSecret = new VaultSecret($httpClient, $requestFactory, $keyVaultName, $token->getAccessToken());

        $secret = $vaultSecret->getSecret($keyVaultSecret, $keyVaultSecretVersion);

        return $this->getAbsoluteTmpPathByContent($secret);
    }

    /**
     * Validates that case data agree with CVR lookup data.
     *
     * @throws CvrException
     */
    public function validateCvr(CaseEntity $case, string $idProperty, string $addressProperty, string $nameProperty): bool
    {
        $caseIdentificationRelevantData = $this->caseManager->getCaseIdentificationValues($case, $addressProperty, $nameProperty);

        /** @var Identification $id */
        $id = $this->propertyAccessor->getValue($case, $idProperty);

        $cvrData = $this->lookupCvr($id->getIdentifier());
        $cvrDataArray = json_decode(json_encode($cvrData), true);

        $cvrIdentificationRelevantData = $this->collectRelevantData($cvrDataArray);

        if ($caseIdentificationRelevantData != $cvrIdentificationRelevantData) {
            throw new CvrException($this->translator->trans('Case data not match CVR register data', [], 'case'));
        }

        $id->setValidatedAt(new \DateTime('now'));
        $this->entityManager->flush();

        return true;
    }

    public function collectRelevantData(array $data): array
    {
        $relevantData = [];

        $relevantData['name'] = $data['virksomhedsnavn']['vaerdi'];
        $relevantData['street'] = $data['beliggenhedsadresse']['CVRAdresse_vejnavn'];
        $relevantData['number'] = array_key_exists('CVRAdresse_husnummerFra', $data['beliggenhedsadresse']) ? $data['beliggenhedsadresse']['CVRAdresse_husnummerFra'] : '';
        $relevantData['floor'] = array_key_exists('CVRAdresse_etagebetegnelse', $data['beliggenhedsadresse']) ? $data['beliggenhedsadresse']['CVRAdresse_etagebetegnelse'] : '';
        $relevantData['side'] = array_key_exists('CVRAdresse_doerbetegnelse', $data['beliggenhedsadresse']) ? $data['beliggenhedsadresse']['CVRAdresse_doerbetegnelse'] : '';
        $relevantData['postalCode'] = $data['beliggenhedsadresse']['CVRAdresse_postnummer'];
        $relevantData['city'] = array_key_exists('CVRAdresse_postdistrikt', $data['beliggenhedsadresse']) ? $data['beliggenhedsadresse']['CVRAdresse_postdistrikt'] : '';

        return $relevantData;
    }

    /**
     * Taken from itk-dev serviceplatformen.
     *
     * @see https://github.com/itk-dev/serviceplatformen/blob/develop/src/Certificate/AzureKeyVaultCertificateLocator.php
     *
     * Creates a temporary file with the provided content and returns the absolute path to the temporary file.
     *
     * The file will be removed from the filesystem when no more references exists to the file.
     *
     * @param string $content the content of the temporary file
     *
     * @return string the absolute path to the temporary file
     */
    private function getAbsoluteTmpPathByContent(string $content): string
    {
        // Static variables is stored in the global variable area and destroyed during the shutdown phase.
        // This ensures that there are no references to the file when the code has executed and thus is deleted.
        // The variable must be declared static before the temporary file is assigned to the variable or else PHP
        // thinks you are assigning values to a constant.
        static $tmpFile = null;
        $tmpFile = tmpfile();
        fwrite($tmpFile, $content);
        $streamMetaData = stream_get_meta_data($tmpFile);

        return $streamMetaData['uri'];
    }
}
