<?php

namespace App\Controller;

use App\Repository\CaseEntityRepository;
use DeepCopy\Filter\ReplaceFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case")
 */
class CaseController extends AbstractController
{
    /**
     * @Route("/", name="case_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('case/index.html.twig', [

        ]);
    }

    /**
     * @Route("/summary", name="case_summary", methods={"GET"})
     */
    public function summary(): Response
    {
        return $this->render('case/summary.html.twig', [

        ]);
    }

    /**
     * @Route("/information", name="case_information", methods={"GET"})
     */
    public function basicInformation(): Response
    {
        return $this->render('case/information.html.twig', [

        ]);
    }
}
