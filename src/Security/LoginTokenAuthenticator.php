<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        // todo
        // brug user repository i stedet for
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request)
    {
        return $request->query->has('loginToken');
    }

    public function getCredentials(Request $request)
    {
        return $request->query->get('loginToken');
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['loginToken' => $credentials]);

        if (!$user) {
            // fail authentication with a custom error
            throw new AuthenticationCredentialsNotFoundException('Token could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // No credentials to check since loginToken login
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // Throw (telling) error
        throw new AuthenticationException('Error occurred validating login token');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // Redirect to some destination - default for now?
        return new RedirectResponse('/');
        // todo
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
