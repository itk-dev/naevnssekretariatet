<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class OpenIdLoginAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function supports(Request $request)
    {
        // Check if request has state and id_token
        return $request->query->has('state') && $request->query->has('id_token');
    }

    public function getCredentials(Request $request)
    {
        // Make sure state and oauth2sate are the same
        if ($request->query->get('state') !== $this->session->get('oauth2state')) {
            $this->session->remove('oauth2state');
            throw new \RuntimeException('Invalid state');
        }

        // Retrieve id_token and decode it
        // @see https://tools.ietf.org/html/rfc7519
        $idToken = $request->query->get('id_token');
        [$jose, $payload, $signature] = array_map('base64_decode', explode('.', $idToken));

        // Figure out where name starts
        $testint = strpos($payload, 'name');

        // Go to first important piece of payload, name
        $usefulStuff = str_split($payload, $testint)[1];

        // Remove special characters
        $usefulStuff = preg_replace('/[:{},]/', '', $usefulStuff);
        $usefulStuff = preg_replace('/(")(")*/', ':', $usefulStuff);

        // Split string, such that we can extract name and upn(email)
        // Can also get AZ-ident if needed in arrayData[5]
        $arrayData = preg_split('/:/', $usefulStuff);
        $name = $arrayData[1];
        $upn = $arrayData[3];
        

        return $payload;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // todo check email osv lav bruger etc...
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // todo
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // todo
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
