<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Identification;
use App\Exception\CvrException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use Itkdev\AzureKeyVault\Authorisation\VaultToken;
use Itkdev\AzureKeyVault\KeyVault\VaultCertificate;
use Itkdev\AzureKeyVault\KeyVault\VaultSecret;
use ItkDev\Serviceplatformen\Certificate\AzureKeyVaultCertificateLocator;
use ItkDev\Serviceplatformen\Certificate\CertificateLocatorInterface;
use ItkDev\Serviceplatformen\Certificate\Exception\CertificateLocatorException;
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
     * Get absolute path to certificate.
     */
    private function getAzureKeyVaultCertificateLocator(
        string $tenantId,
        string $applicationId,
        string $clientSecret,
        string $keyVaultName,
        string $keyVaultSecret,
        string $keyVaultSecretVersion
    ): CertificateLocatorInterface {
        $httpClient = new GuzzleAdapter($this->guzzleClient);
        $requestFactory = new RequestFactory();

        $vaultToken = new VaultToken($httpClient, $requestFactory);

        $token = $vaultToken->getToken(
            $tenantId,
            $applicationId,
            $clientSecret
        );

        // Certificates
        // This requires a PSR-18 compatible http client and a PSR-17 compatible request factory.
        // Get vault with the name 'testVault' using the access token.
        $vaultCertificate = new VaultCertificate($httpClient, $requestFactory, $keyVaultName, $token->getAccessToken());

        $cert = $vaultCertificate->getCertificate($keyVaultSecret, $keyVaultSecretVersion);


//        $vault = new VaultSecret(
//            $httpClient,
//            $requestFactory,
//            $keyVaultName,
//            $token->getAccessToken()
//        );

        return $cert->getCert();

//        return new AzureKeyVaultCertificateLocator(
//            $vault,
//            $keyVaultSecret,
//            $keyVaultSecretVersion
//        );
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
     * @throws CertificateLocatorException
     * @throws GuzzleException
     */
    public function testCvrDatafordeler(string $cvr)
    {
        $certificate = $this->getAzureKeyVaultCertificateLocator(
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
//            'cert' => $certificateLocator->getAbsolutePathToCertificate(),
            'cert' => $certificate,
        ]);

        return (string) $res->getBody();
    }
}
