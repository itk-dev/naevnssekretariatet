<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Repository\DigitalPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/case/{id}/communication/digital-post')]
class DigitalPostController extends AbstractController
{
    #[Route('/', name: 'digital_post_index', methods: ['GET'])]
    public function index(CaseEntity $case, DigitalPostRepository $digitalPostRepository): Response
    {
        return $this->render('case/communication/digital_post/index.html.twig', [
            'case' => $case,
            'digital_posts' => $digitalPostRepository->findByEntity($case),
        ]);
    }

    #[Route('/{digitalPost}', name: 'digital_post_show', methods: ['GET'])]
    public function show(CaseEntity $case, DigitalPost $digitalPost): Response
    {
        return $this->render('case/communication/digital_post/show.html.twig', [
            'case' => $case,
            'digital_post' => $digitalPost,
        ]);
    }
}
