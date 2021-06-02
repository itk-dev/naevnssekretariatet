<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Repository\CaseEntityRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * @Route("/process", name="process")
 */
class ProcessController extends AbstractController
{
    /**
     * @Route("/", name="process_index")
     */
    public function index(CaseEntityRepository $caseEntityRepository): Response
    {
        $processes = $caseEntityRepository->findAll();

        return $this->render('process/index.html.twig', [
            'controller_name' => 'ProcessController',
            'processes' => $processes,
        ]);
    }
}
