<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\OpenIdConnect\Security\OpenIdConfigurationProvider;
use ItkDev\OpenIdConnectBundle\Security\OpenIdLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

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

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, OpenIdConfigurationProvider $provider, RequestStack $requestStack, UrlGeneratorInterface $router, int $leeway = 0)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->router = $router;
        parent::__construct($provider, $requestStack->getSession(), $leeway);
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $name = $credentials['name'];
        $email = $credentials['upn'];
        $roles = $credentials['role'];

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

        return $user;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->router->generate('default'));
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('itkdev_openid_connect_login'));
    }
}
