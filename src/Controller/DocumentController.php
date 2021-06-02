<?php


namespace App\Controller;

use App\Entity\CaseEntity;
use App\Repository\CaseEntityRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/process/{process_id}/documents")
 */
class DocumentController extends AbstractController
{
    /**
     * @Route("/", name="document_index")
     */
    public function index(CaseEntityRepository $caseEntityRepository, string $process_id): Response
    {
        // The beneath can possibly be removed and done via 'guessing' the process instead
        $process = $caseEntityRepository->find(['id' => $process_id]);

        if (null === $process){
            throw new Exception('Process not found');
        }

        echo 'it works';
        die(__FILE__);
    }
}