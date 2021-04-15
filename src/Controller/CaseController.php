<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CaseController extends AbstractController
{
    /**
     * @Route("/case", name="case")
     */
    public function index(): Response
    {
        return $this->render('case/index.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/summary", name="case_summary")
     */
    public function summary(): Response
    {
        return $this->render('case/summary.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/basic-information", name="case_basic_information")
     */
    public function basicInformation(): Response
    {
        return $this->render('case/basic-information.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/status-info", name="case_status_info")
     */
    public function statusInfo(): Response
    {
        return $this->render('case/status-info.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/parties", name="case_parties")
     */
    public function parties(): Response
    {
        return $this->render('case/parties.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/communication", name="case_communication")
     */
    public function communication(): Response
    {
        return $this->render('case/communication.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/documents", name="case_documents")
     */
    public function documents(): Response
    {
        return $this->render('case/documents.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/decision", name="case_decision")
     */
    public function decision(): Response
    {
        return $this->render('case/decision.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/notes", name="case_notes")
     */
    public function notes(): Response
    {
        return $this->render('case/notes.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }

    /**
     * @Route("/case/log", name="case_log")
     */
    public function log(): Response
    {
        return $this->render('case/log.html.twig', [
            'controller_name' => 'CaseController',
        ]);
    }
}
