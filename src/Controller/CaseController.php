<?php

namespace App\Controller;

use App\Repository\CaseEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case", name="case")
 */
class CaseController extends AbstractController
{
    /**
     * @Route("/", name="case_index")
     */
    public function index(CaseEntityRepository $caseEntityRepository): Response
    {
        $cases = $caseEntityRepository->findAll();

        return $this->render('case/index.html.twig', [
            'cases' => $cases,
        ]);
    }
}
