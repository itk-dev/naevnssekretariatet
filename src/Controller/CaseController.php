<?php

namespace App\Controller;

use App\Entity\CaseDecisionProposal;
use App\Entity\CaseEntity;
use App\Entity\CasePresentation;
use App\Form\CaseAgendaStatusType;
use App\Form\CaseAssignCaseworkerType;
use App\Form\CaseDecisionProposalType;
use App\Form\CaseEntityType;
use App\Form\CasePresentationType;
use App\Form\CaseRescheduleFinishProcessDeadlineType;
use App\Form\CaseStatusForm;
use App\Form\Model\CaseStatusFormModel;
use App\Repository\AgendaCaseItemRepository;
use App\Repository\AgendaRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Service\AgendaHelper;
use App\Service\BBRHelper;
use App\Service\CaseHelper;
use App\Service\CaseManager;
use App\Service\WorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

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
     * @Route("/new", name="case_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CaseManager $caseManager): Response
    {
        $form = $this->createForm(CaseEntityType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $caseEntity = $caseManager->newCase(
                $form->get('caseEntity')->getData(),
                $form->get('board')->getData()
            );

            return $this->redirectToRoute('case_show', ['id' => $caseEntity->getId()]);
        }

        return $this->render('case/new.html.twig', [
            'form' => $form->createView(),
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
        $form = $this->createForm('App\\Form\\'.$case->getBoard()->getCaseFormType(), $case, ['board' => $case->getBoard()]);

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
    public function status(CaseEntity $case, AgendaCaseItemRepository $agendaCaseItemRepository, AgendaHelper $agendaHelper, AgendaRepository $agendaRepository, CaseHelper $caseHelper, WorkflowService $workflowService, Request $request): Response
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

        $activeAgendaCaseItems = $agendaCaseItemRepository->findActiveAgendaCaseItemIdsByCase($case);
        $finishedAgendaCaseItems = $agendaCaseItemRepository->findFinishedAgendaCaseItemIdsByCase($case);

        return $this->render('case/status.html.twig', [
            'case' => $case,
            'case_status_form' => $caseStatusForm->createView(),
            'case_agenda_status_form' => $caseAgendaStatusForm->createView(),
            'active_agendas' => $activeAgendaCaseItems,
            'finished_agendas' => $finishedAgendaCaseItems,
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

    /**
     * @Route("/{id}/presentation", name="case_presentation", methods={"GET", "POST"})
     */
    public function presentation(CaseEntity $case, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $casePresentation = $case->getPresentation() ?? new CasePresentation();

        $form = $this->createForm(CasePresentationType::class, $casePresentation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CasePresentation $casePresentation */
            $casePresentation = $form->getData();

            $case->setPresentation($casePresentation);

            $em->persist($casePresentation);
            $em->flush();

            return $this->redirectToRoute('case_presentation', [
                'id' => $case->getId(),
            ]);
        }

        return $this->render('case/presentation.html.twig', [
            'case' => $case,
            'case_presentation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/decision-proposal", name="case_decision_proposal", methods={"GET", "POST"})
     */
    public function decisionProposal(CaseEntity $case, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $caseDecisionProposal = $case->getDecisionProposal() ?? new CaseDecisionProposal();

        $form = $this->createForm(CaseDecisionProposalType::class, $caseDecisionProposal);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CaseDecisionProposal $caseDecisionProposal */
            $caseDecisionProposal = $form->getData();

            $case->setDecisionProposal($caseDecisionProposal);

            $em->persist($caseDecisionProposal);
            $em->flush();

            return $this->redirectToRoute('case_decision_proposal', [
                'id' => $case->getId(),
            ]);
        }

        return $this->render('case/decision_proposal.html.twig', [
            'case' => $case,
            'decision_proposal_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/bbr-meddelelse/{addressProperty}.{_format}", name="case_bbr_meddelelse", methods={"GET"},
     *     format="pdf",
     *     requirements={
     *         "_format": "pdf",
     *     }
     * )
     */
    public function bbrMeddelelse(Request $request, CaseEntity $case, BBRHelper $bbrHelper, string $addressProperty, string $_format): Response
    {
        try {
            return $this->redirect($bbrHelper->getBBRMeddelelseUrlForCase($case, $addressProperty, $_format));
        } catch (\Exception $exception) {
            $this->addFlash('error', new TranslatableMessage('Cannot get url for BBR-Meddelelse'));
        }

        // Send user back to where he came from.
        $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/{id}/bbr-data/{addressProperty}/update", name="case_bbr_data_update", methods={"POST"})
     */
    public function bbrData(Request $request, CaseEntity $case, BBRHelper $bbrHelper, string $addressProperty, EntityManagerInterface $entityManager): Response
    {
        try {
            $bbrHelper->updateCaseBBRData($case, $addressProperty);
            $entityManager->persist($case);
            $entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('BBR data updated'));
        } catch (\Exception $exception) {
            $this->addFlash('error', new TranslatableMessage('Cannot update BBR data'));
        }

        // Send user back to where he came from.
        $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/{id}/reschedule-process-deadline", name="case_reschedule_finish_processing_deadline", methods={"GET","POST"})
     */
    public function rescheduleFinishProcessDeadline(CaseEntity $case, Request $request): Response
    {
        $rescheduleForm = $this->createForm(CaseRescheduleFinishProcessDeadlineType::class, $case);

        $rescheduleForm->handleRequest($request);

        if ($rescheduleForm->isSubmitted() && $rescheduleForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_status', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_reschedule_finish_processing_deadline.html.twig', [
            'reschedule_form' => $rescheduleForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/assign-caseworker", name="case_assign_caseworker", methods={"POST"})
     */
    public function assignCaseworker(CaseEntity $case, UserRepository $userRepository, Request $request): Response
    {
        $availableCaseworkers = $userRepository->findByRole('ROLE_CASEWORKER', ['name' => 'ASC']);

        $assignForm = $this->createForm(CaseAssignCaseworkerType::class, $case, ['available_caseworkers' => $availableCaseworkers]);

        $assignForm->handleRequest($request);

        if ($assignForm->isSubmitted() && $assignForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_index');

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_assign_caseworker.html.twig', [
            'assign_form' => $assignForm->createView(),
            'case' => $case,
        ]);
    }
}
