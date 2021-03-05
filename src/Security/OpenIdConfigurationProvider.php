<?php

declare(strict_types=1);

namespace App\Security;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class OpenIdConfigurationProvider.
 *
 * @see https://github.com/cirrusidentity/simplesamlphp-module-authoauth2/blob/master/lib/Providers/OpenIDConnectProvider.php
 */
class OpenIdConfigurationProvider extends GenericProvider
{
    /**
     * @var string
     */
    protected $urlConfiguration;

    /**
     * @var string
     */
    protected $cachePath;

    /**
     * @var int
     */
    protected $cacheDuration = 24 * 60 * 60;

    /**
     * @var array|null
     */
    private $cache;

    /**
     * {@inheritDoc}
     */
    protected function getConfigurableOptions()
    {
        return array_merge(parent::getConfigurableOptions(), [
            'cacheDuration',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequiredOptions()
    {
        return [
            'urlConfiguration',
            'cachePath',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->getConfiguration('authorization_endpoint');
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl(array $options = [])
    {
        // Add default options scope, response_type and response_mode
        return parent::getAuthorizationUrl($options + [
                'scope' => 'openid',
                'response_type' => 'id_token',
                'response_mode' => 'query',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getConfiguration('token_endpoint');
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getConfiguration('userinfo_endpoint');
    }

    /**
     * Refresh Cache.
     *
     * @throws \Exception
     */
    public function refreshCache(): void
    {
        $response = $this->getHttpClient()->request('GET', $this->urlConfiguration);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Cannot access OpenID configuration resource.');
        }

        try {
            $content = $response->getBody()->getContents();
        } catch (\RuntimeException $e) {
            throw new \Exception('Cannot read OpenID configuration content.');
        }

        if (null === $json = json_decode($content, true)) {
            throw new \Exception('Cannot decode OpenID configuration file.');
        }

        if (false === file_put_contents($this->cachePath, sprintf('<?php return %s;', var_export($json, true)))) {
            throw new \Exception('Cannot save OpenID configuration cache file.');
        }
    }

    /**
     * Get Configuration.
     *
     * @throws \Exception
     */
    private function getConfiguration(string $key): string
    {
        $this->loadCache();

        return $this->cache[$key];
    }

    /**
     * Load Cache.
     *
     * @throws \Exception
     */
    private function loadCache(): void
    {
        if (null === $this->cache) {
            // refresh cache if needed
            if (!file_exists($this->cachePath) || (time() - filemtime($this->cachePath)) > $this->cacheDuration) {
                $this->refreshCache();
            }

            $this->cache = include $this->cachePath;
        }
    }
}
