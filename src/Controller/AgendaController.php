<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\MunicipalityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/agenda")
 */
class AgendaController extends AbstractController
{
    /**
     * @Route("/", name="agenda_index", methods={"GET"})
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

        return $this->render('agenda/index.html.twig', [
            'municipalities' => $municipalities,
            'favorite_municipality' => $favoriteMunicipality,
        ]);
    }

    /**
     * @Route("/create", name="agenda_create", methods={"GET", "POST"})
     */
    public function create(): Response
    {
        return $this->render('agenda/create.html.twig', [
        ]);
    }
}
