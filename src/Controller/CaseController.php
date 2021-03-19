<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaseController extends AbstractController
{
    /**
     * @Route("/case", name="case")
     */
    public function index(): Response
    {
        return $this->render('case/index.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/show", name="case_show")
     */
    public function show(): Response
    {
        return $this->render('case/show.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }
}
