<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Exception\CprException;
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
use ItkDev\Serviceplatformen\Service\Exception\NoPnrFoundException;
use ItkDev\Serviceplatformen\Service\Exception\ServiceException;
use ItkDev\Serviceplatformen\Service\PersonBaseDataExtendedService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CprHelper
{
    /**
     * The client.
     */
    private readonly Client $guzzleClient;
    private readonly array $serviceOptions;
    private PersonBaseDataExtendedService $service;

    public function __construct(private readonly CaseManager $caseManager, private readonly PropertyAccessorInterface $propertyAccessor, private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator, array $options)
    {
        $this->guzzleClient = new Client();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);
    }

    /**
     * @throws CprException
     */
    public function lookupCPR(string $cpr)
    {
        if (!isset($this->service)) {
            $this->setupService();
        }

        try {
            $response = $this->service->personLookup($cpr);
        } catch (NoPnrFoundException $e) {
            throw new CprException($this->translator->trans('PNR not found', [], 'case'), $e->getCode(), $e);
        } catch (ServiceException $e) {
            throw new CprException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * @throws CprException
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
            throw new CprException($e->getMessage(), $e->getCode());
        }

        $options = [
            'local_cert' => $pathToCertificate,
            'passphrase' => $certificateLocator->getPassphrase(),
            'location' => $this->serviceOptions['serviceplatformen_cpr_service_endpoint'],
        ];

        if (!realpath($this->serviceOptions['serviceplatformen_cpr_service_contract'])) {
            throw new CprException(sprintf('The path (%s) to the service contract is invalid.', $this->serviceOptions['serviceplatformen_cpr_service_contract']));
        }

        try {
            $soapClient = new \SoapClient($this->serviceOptions['serviceplatformen_cpr_service_contract'], $options);
        } catch (\SoapFault $e) {
            throw new CprException($e->getMessage(), $e->getCode());
        }

        $requestGenerator = new InvocationContextRequestGenerator(
            $this->serviceOptions['serviceplatformen_cpr_service_agreement_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_user_system_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_service_uuid'],
            $this->serviceOptions['serviceplatformen_cpr_user_uuid']
        );

        $this->service = new PersonBaseDataExtendedService($soapClient, $requestGenerator);
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
                    'serviceplatformen_cpr_service_agreement_uuid',
                    'serviceplatformen_cpr_user_system_uuid',
                    'serviceplatformen_cpr_user_uuid',
                    'serviceplatformen_cpr_service_uuid',
                    'serviceplatformen_cpr_service_endpoint',
                    'serviceplatformen_cpr_service_contract',
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
     * Validates that case data agree with CPR lookup data.
     *
     * @throws CprException
     */
    public function validateCpr(CaseEntity $case, string $idProperty, string $addressProperty, string $addressProtectionProperty, string $nameProperty): bool
    {
        $caseIdentificationRelevantData = $this->caseManager->getCaseIdentificationValues($case, $addressProperty, $nameProperty, $addressProtectionProperty);

        // Get CPR data
        /** @var Identification $id */
        $id = $this->propertyAccessor->getValue($case, $idProperty);

        $cprData = $this->lookupCPR($id->getIdentifier());
        $cprDataArray = json_decode(json_encode($cprData, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $cprIdentificationRelevantData = $this->collectRelevantData($cprDataArray);

        if ($caseIdentificationRelevantData != $cprIdentificationRelevantData) {
            throw new CprException($this->translator->trans('Case data not match CPR data', [], 'case'));
        }

        $id->setValidatedAt(new \DateTime('now'));
        $this->entityManager->flush();

        return true;
    }

    public function collectRelevantData(array $data): array
    {
        $relevantData = [];

        $relevantData['name'] = $data['persondata']['navn']['personadresseringsnavn'];
        $relevantData['street'] = $data['adresse']['aktuelAdresse']['vejadresseringsnavn'];
        $relevantData['number'] = ltrim((string) $data['adresse']['aktuelAdresse']['husnummer'], '0');
        $relevantData['floor'] = array_key_exists('etage', $data['adresse']['aktuelAdresse']) ? $data['adresse']['aktuelAdresse']['etage'] : '';
        $relevantData['side'] = array_key_exists('sidedoer', $data['adresse']['aktuelAdresse']) ? ltrim((string) $data['adresse']['aktuelAdresse']['sidedoer'], '0') : '';
        $relevantData['postalCode'] = $data['adresse']['aktuelAdresse']['postnummer'];
        $relevantData['city'] = $data['adresse']['aktuelAdresse']['postdistrikt'];

        // If person is NOT under address protection, 'adressebeskyttelse' is simply an empty array
        $relevantData['isUnderAddressProtection'] = !empty($data['persondata']['adressebeskyttelse']);

        return $relevantData;
    }

    public function getAddressFromCpr(string $cpr): Address
    {
        $cprObject = $this->lookupCPR($cpr);

        $cprArray = json_decode(json_encode($cprObject, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $street = $cprArray['adresse']['aktuelAdresse']['vejadresseringsnavn'];
        $number = ltrim((string) $cprArray['adresse']['aktuelAdresse']['husnummer'], '0');
        $floor = array_key_exists('etage', $cprArray['adresse']['aktuelAdresse']) ? $cprArray['adresse']['aktuelAdresse']['etage'] : null;
        $side = array_key_exists('sidedoer', $cprArray['adresse']['aktuelAdresse']) ? ltrim((string) $cprArray['adresse']['aktuelAdresse']['sidedoer'], '0') : null;
        $postalCode = $cprArray['adresse']['aktuelAdresse']['postnummer'];
        $city = $cprArray['adresse']['aktuelAdresse']['postdistrikt'];

        $address = (new Address())
            ->setStreet($street)
            ->setNumber($number)
            ->setFloor($floor)
            ->setSide($side)
            ->setPostalCode($postalCode)
            ->setCity($city)
        ;

        return $address;
    }

    /**
     * Removes potential dashes from CPR.
     */
    public function formatIdentifier(string $cpr): string
    {
        return preg_replace('/[^0-9]+/', '', $cpr);
    }
}
