<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\ResidentComplaintBoardCase;
use App\Form\CaseEntityType;
use App\Form\CaseTypeSelectorType;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\MunicipalityRepository;
use App\Service\CaseManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case")
 */
class CaseController extends AbstractController
{
    /**
     * @Route("/", name="case_index", methods={"GET"})
     */
    public function index(CaseEntityRepository $caseEntityRepository): Response
    {
        return $this->render('case/index.html.twig', [
            'case_entities' => $caseEntityRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="case_new", methods={"GET","POST"})
     */
    public function new(): Response
    {
        $form = $this->createForm(CaseTypeSelectorType::class, null);


        return $this->render('case/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="case_show", methods={"GET"})
     */
    public function show(CaseEntity $caseEntity): Response
    {
        return $this->render('case/show.html.twig', [
            'case_entity' => $caseEntity,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="case_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, CaseEntity $caseEntity): Response
    {
        $form = $this->createForm(CaseEntityType::class, $caseEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('case_index');
        }

        return $this->render('case/edit.html.twig', [
            'case_entity' => $caseEntity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="case_delete", methods={"POST"})
     */
    public function delete(Request $request, CaseEntity $caseEntity): Response
    {
        if ($this->isCsrfTokenValid('delete'.$caseEntity->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($caseEntity);
            $entityManager->flush();
        }

        return $this->redirectToRoute('case_index');
    }
}
