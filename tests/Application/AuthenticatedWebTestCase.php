<?php

namespace App\Tests\Application;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected static array $userCriteria;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        return parent::createAuthenticatedClient(static::$userCriteria);
    }
}
