<?php

namespace App\Controller;

use App\Repository\CaseEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/process/{process_id}", name="process")
 */

class ProcessController extends AbstractController
{
    /**
     * @Route("/documents", name="_documents")
     */
    public function documents(CaseEntityRepository $caseRepository, string $process_id): Response
    {
        return $this->render('process/index.html.twig', [
            'controller_name' => 'ProcessController',
        ]);
    }
}
