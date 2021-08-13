<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Form\ResidentComplaintBoardCaseType;
use App\Repository\CaseEntityRepository;
use App\Repository\ComplaintCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(CaseEntityRepository $caseRepository): Response
    {
        $cases = $caseRepository->findAll();

        return $this->render('case/index.html.twig', [
            'cases' => $cases,
        ]);
    }

    /**
     * @Route("/{id}/summary", name="case_summary", methods={"GET"})
     */
    public function summary(CaseEntity $case): Response
    {
        return $this->render('case/summary.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/information", name="case_information", methods={"GET"})
     */
    public function information(CaseEntity $case): Response
    {
        return $this->render('case/information.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/information/edit", name="case_information_edit", methods={"GET", "POST"})
     */
    public function editInformation(CaseEntity $case, Request $request): Response
    {
        // Todo: Handle other case types, possibly via switch on $case->getBoard()->getCaseFormType()
        $form = $this->createForm(ResidentComplaintBoardCaseType::class, $case, ['board' => $case->getBoard()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $case = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('case_information', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        return $this->render('case/information_edit.html.twig', [
            'case' => $case,
            'case_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/status", name="case_status", methods={"GET"})
     */
    public function status(CaseEntity $case): Response
    {
        return $this->render('case/status.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/hearing", name="case_hearing", methods={"GET"})
     */
    public function hearing(CaseEntity $case): Response
    {
        return $this->render('case/hearing.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/communication", name="case_communication", methods={"GET"})
     */
    public function communication(CaseEntity $case): Response
    {
        return $this->render('case/communication.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/decision", name="case_decision", methods={"GET"})
     */
    public function decision(CaseEntity $case): Response
    {
        return $this->render('case/decision.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/notes", name="case_notes", methods={"GET"})
     */
    public function notes(CaseEntity $case): Response
    {
        return $this->render('case/notes.html.twig', [
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/log", name="case_log", methods={"GET"})
     */
    public function log(CaseEntity $case): Response
    {
        return $this->render('case/log.html.twig', [
            'case' => $case,
        ]);
    }
}
