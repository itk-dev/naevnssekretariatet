<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\BoardMemberRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use ItkDev\OpenIdConnectBundle\Security\OpenIdLoginAuthenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class AzureAdLoginAuthenticator extends OpenIdLoginAuthenticator
{
    use TargetPathTrait;

    public function __construct(private EntityManagerInterface $entityManager, private UserRepository $userRepository, private OpenIdConfigurationProviderManager $providerManager, private UrlGeneratorInterface $router, private BoardMemberRepository $boardMemberRepository, private TranslatorInterface $translator, int $leeway = 0)
    {
        parent::__construct($providerManager);
    }

    public function authenticate(Request $request): Passport
    {
        $claims = $this->validateClaims($request);

        $providerKey = $claims['open_id_connect_provider'] ?? null;
        switch ($providerKey) {
            case 'admin':
                return $this->getAdminUser($claims);

            case 'board-member':
                return $this->getBoardMemberUser($claims, $request);
        }

        throw new \RuntimeException(sprintf('Invalid open id connect provider: %s', $providerKey));
    }

    private function getAdminUser(array $claims)
    {
        $name = $claims['name'];
        $email = $claims['upn'];
        $roles = $claims['role'] ?? [];

        // Check if user exists already or create a new user
        $user = $this->userRepository->findOneBy(['email' => $email]) ?? new User();

        // Associative array mapping AD roles to system roles
        $roleArrayAssociative = [
            'SuperAdmin' => 'ROLE_SUPER_ADMIN',
            'Admin' => 'ROLE_ADMIN',
            'Administrator' => 'ROLE_ADMINISTRATION',
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

    private function getBoardMemberUser(array $claims, Request $request)
    {
        $cpr = $claims['cpr'] ?? null;
        $boardMember = $this->boardMemberRepository->findOneBy(['cpr' => $cpr]);
        if (null === $boardMember) {
            // Show a message to the user.
            $message = $this->translator->trans('Access denied', [], 'login');
            try {
                $request->getSession()->getFlashBag()->add('danger', $message);
            } catch (\Exception $exception) {
            }
            // @todo Log this?
            throw new CustomUserMessageAuthenticationException($message);
        }

        $name = $claims['name'];
        $email = $claims['cpr'].'@cpr.example.com';
        $roles = ['ROLE_BOARD_MEMBER'];

        // Check if user exists already or create a new user
        $user = $this->userRepository->findOneBy(['email' => $email]) ?? new User();

        // Update/set names here
        $user->setName($name);
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setBoardMember($boardMember);

        // persist and flush user to database
        // If no change persist will recognize this
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->getTargetPath($request->getSession(), $firewallName) ?? $this->router->generate('default');

        return new RedirectResponse($targetUrl);
    }

    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->router->generate('login'));
    }
}
