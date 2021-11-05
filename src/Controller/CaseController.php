<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\CaseEntity;
use App\Form\CaseAgendaStatusType;
use App\Form\CaseStatusForm;
use App\Form\Model\CaseStatusFormModel;
use App\Form\ResidentComplaintBoardCaseType;
use App\Repository\AgendaRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\NoteRepository;
use App\Service\AgendaHelper;
use App\Service\AgendaStatus;
use App\Service\CaseHelper;
use App\Service\WorkflowService;
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
     * @Route("/{id}/summary", name="case_summary", methods={"GET", "POST"})
     */
    public function summary(CaseEntity $case, NoteRepository $noteRepository, WorkflowService $workflowService, Request $request): Response
    {
        $notes = $noteRepository->findMostRecentNotesByCase($case, 4);

        return $this->render('case/summary.html.twig', [
            'case' => $case,
            'notes' => $notes,
        ]);
    }

    /**
     * @Route("/{id}", name="case_show", methods={"GET"})
     */
    public function show(CaseEntity $case, CaseHelper $casePartyHelper): Response
    {
        $data = $casePartyHelper->getRelevantTemplateAndPartiesByCase($case);

        return $this->render((string) $data['template'], [
            'case' => $case,
            'complainants' => $data['complainants'],
            'counterparties' => $data['counterparties'],
        ]);
    }

    /**
     * @Route("/{id}/edit", name="case_edit", methods={"GET", "POST"})
     */
    public function edit(CaseEntity $case, Request $request): Response
    {
        // Todo: Handle other case types, possibly via switch on $case->getBoard()->getCaseFormType()
        $form = $this->createForm(ResidentComplaintBoardCaseType::class, $case, ['board' => $case->getBoard()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $case = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('case_show', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        return $this->render('case/edit.html.twig', [
            'case' => $case,
            'case_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/status", name="case_status", methods={"GET", "POST"})
     */
    public function status(CaseEntity $case, AgendaHelper $agendaHelper, AgendaRepository $agendaRepository, CaseHelper $caseHelper, WorkflowService $workflowService, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $workflow = $workflowService->getWorkflowForCase($case);

        $caseStatus = new CaseStatusFormModel();
        $caseStatus->setStatus($case->getCurrentPlace());
        $caseStatusForm = $this->createForm(
            CaseStatusForm::class,
            $caseStatus,
            [
                'available_statuses' => $workflowService->getPlaceChoicesForCase($case, $workflow),
            ]
        );

        $caseStatusForm->handleRequest($request);
        if ($caseStatusForm->isSubmitted() && $caseStatusForm->isValid()) {
            $workflow->apply($case, $caseStatus->getStatus());
            $em->persist($case);
            $em->flush();

            return $this->redirectToRoute('case_status', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        $caseAgendaStatusForm = $this->createForm(CaseAgendaStatusType::class, $case);

        $caseAgendaStatusForm->handleRequest($request);
        if ($caseAgendaStatusForm->isSubmitted() && $caseAgendaStatusForm->isValid()) {
            $em->flush();

            return $this->redirectToRoute('case_status', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        // Extract agenda and whether the AgendaCaseItem with current case is an inspection item
        $activeAgendas = [];
        $finishedAgendas = [];

        foreach ($case->getAgendaCaseItems() as $agendaCaseItem) {
            if (AgendaStatus::Finished === $agendaCaseItem->getAgenda()->getStatus()) {
                array_push($finishedAgendas, [
                    'agenda' => $agendaCaseItem->getAgenda(),
                    'isInspection' => $agendaCaseItem->getInspection(),
                ]);
            } else {
                array_push($activeAgendas, [
                    'agenda' => $agendaCaseItem->getAgenda(),
                    'isInspection' => $agendaCaseItem->getInspection(),
                ]);
            }
        }

//        $hasActiveAgenda = $caseHelper->hasActiveAgenda($case);
//
//        $availableOpenAgendas = $agendaHelper->sortAgendasAccordingToDate($availableOpenAgendas);
//
//        $agendaForm = $this->createForm(CaseAgendaSelectType::class, null, [
//            'hasActiveAgenda' => $hasActiveAgenda,
//            'agendas' => $availableOpenAgendas,
//        ]);
//
//        $agendaForm->handleRequest($request);
//        if ($agendaForm->isSubmitted() && $agendaForm->isValid()) {
//            // TODO: redirect to create case item
//            $agenda = $agendaForm->get('agenda')->getData();
//
//            return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
//        }

        return $this->render('case/status.html.twig', [
            'case' => $case,
            'case_status_form' => $caseStatusForm->createView(),
            'case_agenda_status_form' => $caseAgendaStatusForm->createView(),
//            'hasActiveAgenda' => $hasActiveAgenda,
//            'hasOpenAgenda' => $hasOpenAgenda,
//            'agenda_form' => $agendaForm->createView(),
            'active_agendas' => $activeAgendas,
            'finished_agendas' => $finishedAgendas,
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
     * @Route("/{id}/log", name="case_log", methods={"GET"})
     */
    public function log(CaseEntity $case): Response
    {
        return $this->render('case/log.html.twig', [
            'case' => $case,
        ]);
    }
}
