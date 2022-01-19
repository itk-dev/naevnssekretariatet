<?php

namespace App\Controller;

use App\Repository\CaseEntityRepository;
use App\Service\SearchService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search")
     */
    public function index(CaseEntityRepository $caseRepository, PaginatorInterface $paginator, Request $request, SearchService $searchService): Response
    {
        $search = $request->query->get('search');

        $qb = $caseRepository->createQueryBuilder('c');

        $fieldMatches = $searchService->getFieldMatches($search);
        if (count($fieldMatches) > 0) {
            $qb = $searchService->applyFieldSearch($qb, $fieldMatches);
        }

        $escapedSearch = $searchService->escapeStringForLike($search, '\\');

        $qb->orWhere('c.sortingAddress LIKE :search');
        $qb->setParameter(':search', '%'.$escapedSearch.'%' );

        // Add sortable fields.
        $qb->leftJoin('c.complaintCategory', 'complaintCategory');
        $qb->addSelect('partial complaintCategory.{id,name}');

        $pagination = $paginator->paginate(
            $qb->getQuery(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        $pagination->setCustomParameters(['align' => 'center']);

        return $this->render('search/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }
}
