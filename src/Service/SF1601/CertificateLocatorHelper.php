<?php

namespace App\Service\SF1601;

use GuzzleHttp\Client;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use ItkDev\AzureKeyVault\Authorisation\VaultToken;
use ItkDev\AzureKeyVault\KeyVault\VaultSecret;
use ItkDev\Serviceplatformen\Certificate\AzureKeyVaultCertificateLocator;
use ItkDev\Serviceplatformen\Certificate\CertificateLocatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CertificateLocatorHelper
{
    private array $options;

    public function __construct(array $options)
    {
        $this->options = $this->resolveOptions($options);
    }

    /**
     * Get certificate locator.
     */
    public function getCertificateLocator(): CertificateLocatorInterface
    {
        $httpClient = new GuzzleAdapter(new Client());
        $requestFactory = new RequestFactory();

        $vaultToken = new VaultToken($httpClient, $requestFactory);

        $token = $vaultToken->getToken(
            $this->options['tenant_id'],
            $this->options['application_id'],
            $this->options['client_secret'],
        );

        $vault = new VaultSecret(
            $httpClient,
            $requestFactory,
            $this->options['name'],
            $token->getAccessToken()
        );

        return new AzureKeyVaultCertificateLocator(
            $vault,
            $this->options['secret'],
            $this->options['version'],
            $this->options['passphrase'],
        );
    }

    private function resolveOptions(array $options): array
    {
        return (new OptionsResolver())
            ->setRequired([
                'tenant_id',
                'application_id',
                'client_secret',
                'name',
                'secret',
                'version',
            ])
            ->setDefaults([
                'passphrase' => '',
            ])
            ->resolve($options)
        ;
    }
}
