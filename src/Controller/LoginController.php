<?php

namespace App\Controller;

use ItkDev\OpenIdConnectBundle\Exception\InvalidProviderException;
use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Translation\TranslatableMessage;

class LoginController extends AbstractController
{
    use TargetPathTrait;

    private string $authenticationProviderCookieName = 'default_authentication_provider';

    private readonly array $options;

    public function __construct(private readonly OpenIdConfigurationProviderManager $providerManager, array $loginControllerOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($loginControllerOptions);
    }

    #[Route(path: '/login', name: 'login')]
    public function index(Request $request, SessionInterface $session): Response
    {
        $authenticationProviderKey = $this->getAuthenticationProviderKey($request);
        if (null !== $authenticationProviderKey && $this->isValidAuthenticationProvider($authenticationProviderKey)) {
            return $this->redirectToAuthenticationProvider($authenticationProviderKey);
        }

        $rememberProvider = false;

        $formBuilder = $this->createFormBuilder();
        foreach ($this->providerManager->getProviderKeys() as $key) {
            $name = 'open_id_connect.login_provider.'.$key;
            $formBuilder->add('provider_'.$key, SubmitType::class, [
                'label' => new TranslatableMessage($name, [], 'login'),
            ]);
        }
        $form = $formBuilder
            ->add('remember_provider', CheckboxType::class, [
                'label' => new TranslatableMessage('Remember my choice', [], 'login'),
                'required' => false,
            ])
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($this->providerManager->getProviderKeys() as $key) {
                if ($form->get('provider_'.$key)->isClicked()) {
                    $authenticationProviderKey = $key;
                    break;
                }
            }
            $rememberProvider = $form->get('remember_provider')->getData();
        }

        if (null !== $authenticationProviderKey && $this->isValidAuthenticationProvider($authenticationProviderKey)) {
            $response = $this->redirectToAuthenticationProvider($authenticationProviderKey);
            if ($rememberProvider) {
                $response->headers->setCookie(new Cookie(
                    $this->authenticationProviderCookieName,
                    $authenticationProviderKey,
                    new \DateTimeImmutable($this->options['cookie_end_time'])
                ));
            }

            return $response;
        }

        // No valid authentication provider found in request. Let the user pick one.
        return $this->renderForm('login/index.html.twig', [
            'form' => $form,
        ]);
    }

    private function redirectToAuthenticationProvider(string $key): Response
    {
        return $this->redirectToRoute('itkdev_openid_connect_login', ['providerKey' => $key]);
    }

    /**
     * Get authentication provider key from request.
     */
    private function getAuthenticationProviderKey(Request $request): ?string
    {
        // Look for authentication provider (aliased to "role") in query string.
        if ($request->query->has('role')) {
            return $request->query->get('role');
        }

        // Look for authentication provider (aliased to "role") in target path query string.
        $targetPath = $this->getTargetPath($request->getSession(), $this->options['firewall_name']);
        if (null !== $targetPath) {
            $queryString = parse_url($targetPath, PHP_URL_QUERY);
            if (null !== $queryString) {
                parse_str($queryString, $targetQuery);

                return $targetQuery['role'] ?? null;
            }
        }

        // Get authentication provider from cookie.
        if (null === $request->get('reset-authentication-provider')) {
            return $request->cookies->get($this->authenticationProviderCookieName);
        }

        // No provider key found.
        return null;
    }

    /**
     * Check if an OpenID configuration key is valid, i.e. if the provider exists.
     */
    private function isValidAuthenticationProvider(?string $key): bool
    {
        try {
            $this->providerManager->getProvider($key ?? '');

            return true;
        } catch (InvalidProviderException) {
        }

        return false;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('firewall_name')
            ->setAllowedTypes('firewall_name', 'string')
            ->setRequired('cookie_end_time')
            ->setAllowedValues('cookie_end_time', function ($value) {
                try {
                    new \DateTimeImmutable($value);

                    return true;
                } catch (\Exception) {
                    return false;
                }
            })
        ;
    }
}
