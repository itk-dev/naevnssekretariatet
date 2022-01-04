<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use ItkDev\OpenIdConnectBundle\Security\OpenIdLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AzureAdLoginAuthenticator extends OpenIdLoginAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, OpenIdConfigurationProviderManager $providerManager, SessionInterface $session, UrlGeneratorInterface $router, int $leeway = 0)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->router = $router;
        parent::__construct($providerManager, $session, $leeway);
    }

    public function authenticate(Request $request): Passport
    {
        $claims = $this->getClaims($request);

        $name = $claims['name'];
        $email = $claims['upn'];
        $roles = $claims['role'];

        //Check if user exists already - if not create a user
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            // Create the new user
            $user = new User();
        }

        // Associative array mapping AD roles to system roles
        $roleArrayAssociative = [
            'SuperAdmin' => 'ROLE_SUPER_ADMIN',
            'Admin' => 'ROLE_ADMIN',
            'Administrator' => 'ROLE_ADMINISTRATOR',
            'Sagsbehandler' => 'ROLE_CASEWORKER',
        ];

        $mappedRoles = [];
        foreach ($roles as $role) {
            $mappedRoles[] = $roleArrayAssociative[$role];
        }

        // Update/set names here
        $user->setName($name);
        $user->setEmail($email);
        $user->setRoles($mappedRoles);

        // persist and flush user to database
        // If no change persist will recognize this
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('default'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('itkdev_openid_connect_login', ['providerKey' => 'admin']));
    }
}
