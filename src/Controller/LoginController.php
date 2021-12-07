<?php

namespace App\Controller;

use ItkDev\OpenIdConnectBundle\Security\OpenIdConfigurationProviderManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
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
        return $this->render('login/index.html.twig', [
            'provider_keys' => $this->providerManager->getProviderKeys(),
        ]);
    }
}
