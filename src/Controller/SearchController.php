<?php

namespace App\Controller;

use App\Repository\CaseEntityRepository;
use App\Service\SearchService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(CaseEntityRepository $caseRepository, PaginatorInterface $paginator, Request $request, SearchService $searchService)
    {
        $search = $request->query->get('search');

        $qb = $caseRepository->createQueryBuilder('c');

        $fieldMatches = $searchService->getFieldMatches($search);

        if (sizeof($fieldMatches) > 0) {
            $qb = $searchService->applyFieldSearch($qb, $fieldMatches);
        }

        $query = $qb->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('search/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }
}
