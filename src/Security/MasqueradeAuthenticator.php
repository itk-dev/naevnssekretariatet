<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class MasqueradeAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return $request->query->has('masquerade');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        throw new AuthenticationException('Error occurred while authenticating', $exception->getCode(), $exception);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        // Redirect to some destination - default for now?
        return new RedirectResponse('/');
        // todo
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->query->get('masquerade');

        return new SelfValidatingPassport(new UserBadge($email), []);
    }
}
