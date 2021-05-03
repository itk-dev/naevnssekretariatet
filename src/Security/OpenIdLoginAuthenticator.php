<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\MunicipalityRepository;
use App\Repository\UserRepository;
use App\Util\MunicipalityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var MunicipalityHelper
     */
    private $municipalityHelper;

    /**
     * @var MunicipalityRepository
     */
    private $municipalityRepository;
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, MunicipalityHelper $municipalityHelper, MunicipalityRepository $municipalityRepository, SessionInterface $session, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->municipalityHelper = $municipalityHelper;
        $this->municipalityRepository = $municipalityRepository;
        $this->session = $session;
        $this->userRepository = $userRepository;
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

        return json_decode($payload, true);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $name = $credentials['name'];
        $email = $credentials['upn'];

        //Check if user exists already - if not create a user
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            // Create the new user
            $user = new User();
        }

        // Update/set names here
        $user->setName($name);
        $user->setEmail($email);
        // todo - roles must be extracted from credentials at a later stage
        // $newUser->setRoles(['ROLE_ADMIN']);

        // persist and flush user to database
        // If no change persist will recognize this
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // Throw (telling) error
        throw new AuthenticationException('Error occurred validating azure login');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // todo Redirect to some destination - default for now?
        return new RedirectResponse('/');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/login');
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
