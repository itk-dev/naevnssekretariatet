<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Identification;
use App\Exception\CvrException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use ItkDev\Serviceplatformen\Certificate\AzureKeyVaultCertificateLocator;
use ItkDev\Serviceplatformen\Certificate\CertificateLocatorInterface;
use ItkDev\Serviceplatformen\Certificate\Exception\CertificateLocatorException;
use ItkDev\Serviceplatformen\Request\InvocationContextRequestGenerator;
use ItkDev\Serviceplatformen\Service\Exception\NoCvrFoundException;
use ItkDev\Serviceplatformen\Service\Exception\ServiceException;
use ItkDev\Serviceplatformen\Service\OnlineService;
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
    private OnlineService $service;

    public function __construct(private CaseManager $caseManager, private PropertyAccessorInterface $propertyAccessor, private EntityManagerInterface $entityManager, private TranslatorInterface $translator, array $options)
    {
        $this->guzzleClient = new Client();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @throws CvrException
     */
    public function lookupCvr(string $cvr)
    {
        if (!isset($this->service)) {
            $this->setupService();
        }

        try {
            $response = $this->service->getLegalUnit($cvr);
        } catch (NoCvrFoundException $e) {
            throw new CvrException($this->translator->trans('CVR not found', [], 'case'), $e->getCode(), $e);
        } catch (ServiceException $e) {
            throw new CvrException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * @throws CvrException
     */
    private function setupService()
    {
        $certificateLocator = $this->getAzureKeyVaultCertificateLocator(
            $this->serviceOptions['azure_tenant_id'],
            $this->serviceOptions['azure_application_id'],
            $this->serviceOptions['azure_client_secret'],
            $this->serviceOptions['azure_key_vault_name'],
            $this->serviceOptions['azure_key_vault_secret'],
            $this->serviceOptions['azure_key_vault_secret_version']
        );

        try {
            $pathToCertificate = $certificateLocator->getAbsolutePathToCertificate();
        } catch (CertificateLocatorException $e) {
            throw new CvrException($e->getMessage(), $e->getCode());
        }

        $options = [
            'local_cert' => $pathToCertificate,
            'passphrase' => $certificateLocator->getPassphrase(),
            'location' => $this->serviceOptions['serviceplatformen_cvr_service_endpoint'],
        ];

        if (!realpath($this->serviceOptions['serviceplatformen_cvr_service_contract'])) {
            throw new CvrException(sprintf('The path (%s) to the service contract is invalid.', $this->serviceOptions['serviceplatformen_cvr_service_contract']));
        }

        try {
            $soapClient = new \SoapClient($this->serviceOptions['serviceplatformen_cvr_service_contract'], $options);
        } catch (\SoapFault $e) {
            throw new CvrException($e->getMessage(), $e->getCode());
        }

        $requestGenerator = new InvocationContextRequestGenerator(
            $this->serviceOptions['serviceplatformen_cvr_service_agreement_uuid'],
            $this->serviceOptions['serviceplatformen_cvr_user_system_uuid'],
            $this->serviceOptions['serviceplatformen_cvr_service_uuid'],
            $this->serviceOptions['serviceplatformen_cvr_user_uuid']
        );

        $this->service = new OnlineService($soapClient, $requestGenerator);
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'azure_tenant_id',
                'azure_application_id',
                'azure_client_secret',
                'azure_key_vault_name',
                'azure_key_vault_secret',
                'azure_key_vault_secret_version',
                'serviceplatformen_cvr_service_agreement_uuid',
                'serviceplatformen_cvr_user_system_uuid',
                'serviceplatformen_cvr_user_uuid',
                'serviceplatformen_cvr_service_uuid',
                'serviceplatformen_cvr_service_endpoint',
                'serviceplatformen_cvr_service_contract',
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

        $vault = new VaultSecret(
            $httpClient,
            $requestFactory,
            $keyVaultName,
            $token->getAccessToken()
        );

        return new AzureKeyVaultCertificateLocator(
            $vault,
            $keyVaultSecret,
            $keyVaultSecretVersion
        );
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

        $relevantData['name'] = $data['GetLegalUnitResponse']['LegalUnit']['LegalUnitName']['name'];
        $relevantData['street'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['StreetName'];
        $relevantData['number'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['StreetBuildingIdentifier'];
        $relevantData['floor'] = array_key_exists('FloorIdentifier', $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']) ? $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['FloorIdentifier'] : '';
        $relevantData['side'] = array_key_exists('SuiteIdentifier', $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']) ? $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['SuiteIdentifier'] : '';
        $relevantData['postalCode'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['PostCodeIdentifier'];
        $relevantData['city'] = $data['GetLegalUnitResponse']['LegalUnit']['AddressOfficial']['AddressPostalExtended']['DistrictName'];

        return $relevantData;
    }
}
