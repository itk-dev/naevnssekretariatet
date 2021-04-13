<?php

namespace App\Controller;

use App\Security\OpenIdConfigurationProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(SessionInterface $session, array $openIdProviderOptions = []): Response
    {
        $provider = new OpenIdConfigurationProvider([
            'redirectUri' => $this->generateUrl('default', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ] + $openIdProviderOptions);

        $authUrl = $provider->getAuthorizationUrl();

        $session->set('oauth2state', $provider->getState());

        return new RedirectResponse($authUrl);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): Response
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

}
