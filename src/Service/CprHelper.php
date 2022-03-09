<?php

namespace App\Service;

use App\Exception\CprException;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use ItkDev\Serviceplatformen\Certificate\AzureKeyVaultCertificateLocator;
use ItkDev\Serviceplatformen\Certificate\CertificateLocatorInterface;
use ItkDev\Serviceplatformen\Certificate\Exception\CertificateLocatorException;
use ItkDev\Serviceplatformen\Request\InvocationContextRequestGenerator;
use ItkDev\Serviceplatformen\Service\Exception\ServiceException;
use ItkDev\Serviceplatformen\Service\PersonBaseDataExtendedService;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CprHelper
{
    /**
     * The client.
     */
    private Client $guzzleClient;
    private array $serviceOptions;
    private PersonBaseDataExtendedService $service;

    public function __construct(private PropertyAccessorInterface $propertyAccessor, array $options)
    {
        $this->guzzleClient = new Client();
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->serviceOptions = $resolver->resolve($options);

        $this->service = $this->setupService();
    }

    public function lookupCPR(string $cpr)
    {
        try {
            $response = $this->service->personLookup($cpr);
        } catch (ServiceException $e) {
            throw new CprException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

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

        return new PersonBaseDataExtendedService($soapClient, $requestGenerator);
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
}
