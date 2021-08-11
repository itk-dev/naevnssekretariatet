<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case/{id}/documents")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="case_documents", methods={"GET"})
     */
    public function index(CaseEntity $case): Response
    {
        return $this->render('documents/index.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/create", name="case_documents_create", methods={"GET"})
     */
    public function create(CaseEntity $case): Response
    {
        return $this->render('documents/create.html.twig', [
            'case' => $case,
        ]);
    }
}
