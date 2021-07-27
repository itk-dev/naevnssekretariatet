<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case/documents")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="case_documents", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('documents/index.html.twig', [

        ]);
    }

    /**
     * @Route("/create", name="case_documents_create", methods={"GET"})
     */
    public function create(): Response
    {
        return $this->render('documents/create.html.twig', [

        ]);
    }
}
