<?php

namespace App\Tests\Application;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

abstract class WebTestCase extends BaseWebTestCase
{
    protected static function createAuthenticatedClient(array $userCriteria, array $options = [], array $server = []): KernelBrowser
    {
        // Important: We MUST use self::createClient() (as opposed to
        // static::createClient()) here to prevent an infinite loop when
        // calling AuthenticatedWebTestCase::createClient().
        $client = self::createClient($options, $server);

        return static::loginUser($userCriteria, $client);
    }

    /**
     * @see https://symfony.com/doc/current/testing.html#logging-in-users-authentication
     */
    protected static function loginUser(array $userCriteria, KernelBrowser $client): KernelBrowser
    {
        $repository = static::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy($userCriteria);

        if (null === $user) {
            throw new UserNotFoundException(json_encode($userCriteria));
        }

        return $client->loginUser($user);
    }
}
