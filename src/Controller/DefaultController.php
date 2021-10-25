<?php

namespace App\Controller;

use App\Entity\Municipality;
use App\Entity\User;
use App\Repository\MunicipalityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(MunicipalityRepository $municipalityRepository, Security $security): Response
    {
        // Get current User
        /** @var User $user */
        $user = $security->getUser();

        // Get favorite municipality
        // null is fine as it is only used for selecting an option
        $favoriteMunicipality = $user->getFavoriteMunicipality();

        // Get municipalities
        $municipalities = $municipalityRepository->findAll();

        return $this->render('dashboard/index.html.twig', [
            'municipalities' => $municipalities,
            'favorite_municipality' => $favoriteMunicipality,
        ]);
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
