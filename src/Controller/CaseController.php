<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
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
    public function information(): Response
    {
        return $this->render('case/information.html.twig', [

        ]);
    }

    /**
     * @Route("/status", name="case_status", methods={"GET"})
     */

    public function status(): Response
    {
        return $this->render('case/status.html.twig', [

        ]);
    }

    /**
     * @Route("/hearing", name="case_hearing", methods={"GET"})
     */
    public function hearing(): Response
    {
        return $this->render('case/hearing.html.twig', [

        ]);
    }

    /**
     * @Route("/communication", name="case_communication", methods={"GET"})
     */
    public function communication(): Response
    {
        return $this->render('case/communication.html.twig', [

        ]);
    }

    /**
     * @Route("/decision", name="case_decision", methods={"GET"})
     */
    public function decision(): Response
    {
        return $this->render('case/decision.html.twig', [

        ]);
    }

    /**
     * @Route("/notes", name="case_notes", methods={"GET"})
     */
    public function notes(): Response
    {
        return $this->render('case/notes.html.twig', [

        ]);
    }

    /**
     * @Route("/log", name="case_log", methods={"GET"})
     */
    public function log(): Response
    {
        return $this->render('case/log.html.twig', [

        ]);
    }
}
