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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Translation\TranslatableMessage;

class LoginController extends AbstractController
{
    use TargetPathTrait;

    private OpenIdConfigurationProviderManager $providerManager;

    public function __construct(OpenIdConfigurationProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     * @Route("/login", name="login")
     */
    public function index(Request $request, SessionInterface $session): Response
    {
        $authenticationProvider = null;

        // Look for authentication provider (aliased to "role") in query string.
        if (null !== $request->query->has('role')) {
            $authenticationProvider = $request->query->get('role');
        }

        // Look for authentication provider (aliased to "role") in target path query string.
        if (null === $authenticationProvider) {
            $targetPath = $this->getTargetPath($request->getSession(), 'main');
            if (null !== $targetPath) {
                $queryString = parse_url($targetPath, PHP_URL_QUERY);
                if (null !== $queryString) {
                    parse_str($queryString, $targetQuery);
                    $authenticationProvider = $targetQuery['role'] ?? null;
                }
            }
        }

        // Get authentication provider from cookie.
        if (null === $authenticationProvider) {
            $authenticationProviderCookieName = 'authentication_provider';
            if (null === $request->get('reset-authentication-provider')) {
                $authenticationProvider = $request->cookies->get($authenticationProviderCookieName);
            }
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
                    $authenticationProvider = $key;
                    break;
                }
            }
            $rememberProvider = $form->get('remember_provider')->getData();
        }

        if (null !== $authenticationProvider) {
            try {
                $this->providerManager->getProvider($authenticationProvider);
                $response = $this->redirectToRoute('itkdev_openid_connect_login',
                    ['providerKey' => $authenticationProvider]);
                if ($rememberProvider) {
                    $response->headers->setCookie(new Cookie($authenticationProviderCookieName, $authenticationProvider, new \DateTimeImmutable('+1 month')));
                }

                return $response;
            } catch (InvalidProviderException $invalidProviderException) {
            }
        }

        return $this->renderForm('login/index.html.twig', [
            'form' => $form,
        ]);
    }
}
