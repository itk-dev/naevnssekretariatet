<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Form\CaseEntityType;
use App\Form\CaseStatusForm;
use App\Form\Model\CaseStatusFormModel;
use App\Repository\CaseEntityRepository;
use App\Repository\NoteRepository;
use App\Service\BBRHelper;
use App\Service\CaseHelper;
use App\Service\CaseManager;
use App\Service\WorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($caseEntity);
            $entityManager->flush();

            return $this->redirectToRoute('case_show', ['id' => $caseEntity->getId()]);
        }

        return $this->render('case/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="case_show", methods={"GET"})
     */
    public function show(CaseEntity $case, CaseHelper $casePartyHelper, TranslatorInterface $translator): Response
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
    public function status(CaseEntity $case, WorkflowService $workflowService, Request $request): Response
    {
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
            $em = $this->getDoctrine()->getManager();
            $em->persist($case);
            $em->flush();

            return $this->redirectToRoute('case_status', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        return $this->render('case/status.html.twig', [
            'case' => $case,
            'case_status_form' => $caseStatusForm->createView(),
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
     * @Route("/{id}/bbr-meddelelse/{addressProperty}.{_format}", name="case_bbr_meddelelse", methods={"GET"},
     *     format="pdf",
     *     requirements={
     *         "_format": "pdf",
     *     }
     * )
     */
    public function bbrMeddelelse(Request $request, TranslatorInterface $translator, CaseEntity $case, BBRHelper $bbrHelper, string $addressProperty, string $_format): Response
    {
        try {
            return $this->redirect($bbrHelper->getBBRMeddelelseUrlForCase($case, $addressProperty, $_format));
        } catch (\Exception $exception) {
            $this->addFlash('error', $translator->trans('Cannot get url for BBR-Meddelelse (%message%)', [
                '%message%' => $exception->getMessage(),
            ], 'case'));
        }

        // Send user back to where he came from.
        $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

        return $this->redirect($redirectUrl);
    }

    /**
     * @Route("/{id}/bbr-data/{addressProperty}/update", name="case_bbr_data_update", methods={"POST"})
     */
    public function bbrData(Request $request, CaseEntity $case, BBRHelper $bbrHelper, string $addressProperty, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        try {
            $bbrHelper->updateCaseBBRData($case, $addressProperty);
            $entityManager->persist($case);
            $entityManager->flush();
        } catch (\Exception $exception) {
            $this->addFlash('error', $translator->trans('Cannot update BBR data (%message%)', [
                '%message%' => $exception->getMessage(),
            ], 'case'));
        }

        // Send user back to where he came from.
        $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

        return $this->redirect($redirectUrl);
    }
}
