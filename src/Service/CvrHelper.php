<?php

namespace App\Service;

use App\Exception\CvrException;
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

class CvrHelper
{
    /**
     * The client.
     */
    private Client $guzzleClient;
    private array $serviceOptions;

    public function __construct(array $options)
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
//                'datafordeler_api_username',
//                'datafordeler_api_password',
            ],
            )
        ;
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

//    /**
//     * Validates that case data agree with CVR lookup data.
//     *
//     * @throws CvrException
//     */
//    public function validateCvr(CaseEntity $case, string $idProperty, string $addressProperty, string $nameProperty): bool
//    {
//        $caseIdentificationRelevantData = $this->caseManager->getCaseIdentificationValues($case, $addressProperty, $nameProperty);
//
//        /** @var Identification $id */
//        $id = $this->propertyAccessor->getValue($case, $idProperty);
//
//        $cvrData = $this->lookupCvr($id->getIdentifier());
//        $cvrDataArray = json_decode(json_encode($cvrData), true);
//
//        $cvrIdentificationRelevantData = $this->collectRelevantData($cvrDataArray);
//
//        if ($caseIdentificationRelevantData != $cvrIdentificationRelevantData) {
//            throw new CvrException($this->translator->trans('Case data not match CVR register data', [], 'case'));
//        }
//
//        $id->setValidatedAt(new \DateTime('now'));
//        $this->entityManager->flush();
//
//        return true;
//    }

//    public function collectRelevantData(array $data): array
//    {
//        $relevantData = [];
//
//        $relevantData['name'] = $data['GetLegalUnitResponse']['LegalUnit']['LegalUnitName']['name'];
//        $relevantData['street'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['StreetName'];
//        $relevantData['number'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['StreetBuildingIdentifier'];
//        $relevantData['floor'] = array_key_exists('FloorIdentifier', $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']) ? $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['FloorIdentifier'] : '';
//        $relevantData['side'] = array_key_exists('SuiteIdentifier', $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']) ? $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['SuiteIdentifier'] : '';
//        $relevantData['postalCode'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['PostCodeIdentifier'];
//        $relevantData['city'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['DistrictName'];
//
//        return $relevantData;
//    }

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

        return (string) $res->getBody();
    }
}
