<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\CaseDecisionProposal;
use App\Entity\CaseEntity;
use App\Entity\CasePresentation;
use App\Entity\LogEntry;
use App\Entity\Municipality;
use App\Entity\User;
use App\Exception\BoardMemberException;
use App\Form\CaseAgendaStatusType;
use App\Form\CaseAssignCaseworkerType;
use App\Form\CaseDecisionProposalType;
use App\Form\CaseDeleteType;
use App\Form\CaseFilterType;
use App\Form\CaseMoveType;
use App\Form\CasePresentationType;
use App\Form\CaseRescheduleFinishHearingDeadlineType;
use App\Form\CaseRescheduleFinishProcessDeadlineType;
use App\Form\CaseStatusForm;
use App\Form\Model\CaseStatusFormModel;
use App\Form\MunicipalitySelectorType;
use App\Form\NewCaseMunicipalityAndBoardType;
use App\Repository\AgendaCaseItemRepository;
use App\Repository\BoardRepository;
use App\Repository\CaseEntityRepository;
use App\Repository\DigitalPostRepository;
use App\Repository\LogEntryRepository;
use App\Repository\MunicipalityRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Service\AddressHelper;
use App\Service\BBRHelper;
use App\Service\BoardHelper;
use App\Service\CaseManager;
use App\Service\IdentificationHelper;
use App\Service\LogEntryHelper;
use App\Service\MunicipalityHelper;
use App\Service\PartyHelper;
use App\Service\WorkflowService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/case")
 */
class CaseController extends AbstractController
{
    /**
     * @Route("/", name="case_index", methods={"GET", "POST"})
     */
    public function index(CaseEntityRepository $caseRepository, FilterBuilderUpdaterInterface $filterBuilderUpdater, MunicipalityHelper $municipalityHelper, MunicipalityRepository $municipalityRepository, PaginatorInterface $paginator, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION') || $this->isGranted('ROLE_BOARD_MEMBER'))) {
            throw new AccessDeniedException();
        }

        $activeMunicipality = $municipalityHelper->getActiveMunicipality();
        $municipalities = $municipalityRepository->findBy([], ['name' => 'ASC']);

        $municipalityForm = $this->createForm(MunicipalitySelectorType::class, null, [
            'municipalities' => $municipalities,
            'active_municipality' => $activeMunicipality,
        ]);

        $municipalityForm->handleRequest($request);
        if ($municipalityForm->isSubmitted()) {
            $municipality = $municipalityForm->get('municipality')->getData();

            $municipalityHelper->setActiveMunicipalitySession($municipality);

            return $this->redirectToRoute('case_index');
        }

        // Setup filter and pagination
        // If user is a board member we have to modify list of cases and filters shown
        if ($this->isGranted('ROLE_BOARD_MEMBER')) {
            /** @var User $user */
            $user = $this->getUser();

            $boardMember = $user->getBoardMember();

            if (null === $boardMember) {
                $message = sprintf('User %s is not linked to any board member.', $user->getName());
                throw new BoardMemberException($message);
            }

            $filterBuilder = $caseRepository->createQueryBuilderForBoardMember($boardMember);

            $filterOptions = [
                'municipality' => $activeMunicipality,
                'isBoardMember' => true,
            ];
        } else {
            $filterBuilder = $caseRepository->createQueryBuilder('c');

            $filterOptions = [
                'municipality' => $activeMunicipality,
                'isBoardMember' => false,
            ];
        }

        $filterForm = $this->createForm(CaseFilterType::class, null, $filterOptions);

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
        }

        $filterBuilderUpdater->addFilterConditions($filterForm, $filterBuilder);

        // Add sortable fields depending on case type.
        $filterBuilder->leftJoin('c.complaintCategory', 'complaintCategory');
        $filterBuilder->addSelect('partial complaintCategory.{id,name}');

        // Only get agendas under active municipality
        $filterBuilder->andWhere('c.municipality = :municipality')
            ->setParameter('municipality', $activeMunicipality->getId()->toBinary())
        ;

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            [
                'defaultSortFieldName' => 'c.caseNumber',
                'defaultSortDirection' => 'ASC',
            ]
        );

        $pagination->setCustomParameters(['align' => 'center']);

        return $this->render('case/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'municipalities' => $municipalities,
            'pagination' => $pagination,
            'municipality_form' => $municipalityForm->createView(),
        ]);
    }

    /**
     * @Route("/new", name="case_new_prepare", methods={"GET", "POST"})
     */
    public function newPrepare(MunicipalityHelper $municipalityHelper, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        // Find active municipality with purpose of preselecting it in case creation form
        $activeMunicipality = $municipalityHelper->getActiveMunicipality();

        $form = $this->createForm(NewCaseMunicipalityAndBoardType::class, null, [
            'active_municipality' => $activeMunicipality,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $municipality = $form->getData()['municipality'];
            $board = $form->getData()['board'];

            return $this->redirectToRoute('case_new', ['municipality_id' => $municipality->getId(), 'board_id' => $board->getId()]);
        }

        return $this->render('case/new_prepare.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/{municipality_id}/{board_id}", name="case_new", methods={"GET", "POST"})
     * @Entity("board", expr="repository.find(board_id)")
     */
    public function new(Board $board, BoardHelper $boardHelper, Request $request, CaseManager $caseManager): Response
    {
        $case = $boardHelper->createNewCase($board);

        $this->denyAccessUnlessGranted('edit', $case);

        // Todo: Handle other case types, possibly via switch on $case->getBoard()->getCaseFormType()
        $form = $this->createForm('App\\Form\\'.$board->getCaseFormType(), $case, ['board' => $board]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $caseEntity = $caseManager->newCase(
                $case,
                $board
            );

            return $this->redirectToRoute('case_show', ['id' => $caseEntity->getId()]);
        }

        return $this->render('case/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/summary", name="case_summary", methods={"GET", "POST"})
     */
    public function summary(BoardRepository $boardRepository, CaseEntity $case, NoteRepository $noteRepository, DigitalPostRepository $digitalPostRepository): Response
    {
        $this->denyAccessUnlessGranted('view', $case);

        $numberOfShownItems = 4;

        $notes = $noteRepository->findMostRecentNotesByCase($case, $numberOfShownItems);
        $communications = $digitalPostRepository->findByEntity($case, [], ['createdAt' => Criteria::DESC], $numberOfShownItems);

        $suitableBoards = $boardRepository->findDifferentSuitableBoards($case->getBoard());

        return $this->render('case/summary.html.twig', [
            'case' => $case,
            'notes' => $notes,
            'communications' => $communications,
            'suitable_boards' => $suitableBoards,
            'is_deletable' => $this->isDeletable($case),
        ]);
    }

    /**
     * @Route("/{id}", name="case_show", methods={"GET"})
     */
    public function show(CaseEntity $case, PartyHelper $partyHelper): Response
    {
        $this->denyAccessUnlessGranted('view', $case);

        $parties = $partyHelper->getRelevantPartiesByCase($case);

        return $this->render('case/show.html.twig', [
            'case' => $case,
            'parties' => $parties['parties'],
            'counterparties' => $parties['counterparties'],
        ]);
    }

    /**
     * @Route("/{id}/edit", name="case_edit", methods={"GET", "POST"})
     */
    public function edit(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Todo: Handle other case types, possibly via switch on $case->getBoard()->getCaseFormType()
        $form = $this->createForm('App\\Form\\'.$case->getBoard()->getCaseFormType(), $case, ['board' => $case->getBoard()]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $case = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', new TranslatableMessage('Case updated', [], 'case'));

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
    public function status(CaseEntity $case, AgendaCaseItemRepository $agendaCaseItemRepository, WorkflowService $workflowService, Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', $case);

        $em = $this->getDoctrine()->getManager();
        $workflow = $workflowService->getWorkflowForCase($case);

        $caseStatus = new CaseStatusFormModel();
        $caseStatus->setStatus($case->getCurrentPlace());
        $caseStatusForm = $this->createForm(
            CaseStatusForm::class,
            $caseStatus,
            [
                'available_statuses' => $workflowService->getPlaceChoicesForCase($case, $workflow),
                'current_status' => $case->getCurrentPlace(),
            ]
        );

        $caseStatusForm->handleRequest($request);
        if ($caseStatusForm->isSubmitted() && $caseStatusForm->isValid()) {
            $workflow->apply($case, $caseStatus->getStatus());
            $em->persist($case);
            $em->flush();
            $this->addFlash('success', new TranslatableMessage('Case status updated', [], 'case'));

            return $this->redirectToRoute('case_status', [
                'id' => $case->getId(),
                'case' => $case,
            ]);
        }

        $caseAgendaStatusForm = $this->createForm(CaseAgendaStatusType::class, $case);

        $caseAgendaStatusForm->handleRequest($request);
        if ($caseAgendaStatusForm->isSubmitted() && $caseAgendaStatusForm->isValid()) {
            $em->flush();
            $this->addFlash('success', new TranslatableMessage('Case agenda status updated', [], 'case'));

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
     * @Route("/{id}/communication", name="case_communication", methods={"GET"})
     */
    public function communication(CaseEntity $case): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        return $this->redirectToRoute('digital_post_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/{id}/log", name="case_log", methods={"GET"})
     */
    public function log(CaseEntity $case, LogEntryRepository $logEntryRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $logEntries = $logEntryRepository->findBy([
            'caseID' => $case->getId(),
        ], [
            // The id is a ULID (Universally Unique Lexicographically Sortable Identifier) (cf. https://symfony.com/doc/current/components/uid.html#ulids)
            'id' => Criteria::DESC,
        ]);

        return $this->render('case/log.html.twig', [
            'case' => $case,
            'log_entries' => $logEntries,
        ]);
    }

    /**
     * @Route("/{case}/log/{logEntry}", name="case_log_entry_show", methods={"GET"})
     */
    public function logEntryShow(Request $request, CaseEntity $case, LogEntry $logEntry, LogEntryRepository $logEntryRepository, LogEntryHelper $logEntryHelper): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $urls = [];
        if (null !== ($previousLogEntry = $logEntryRepository->findPrevious($case, $logEntry))) {
            $urls['previous'] = $this->generateUrl('case_log_entry_show', ['case' => $case->getId(), 'logEntry' => $previousLogEntry->getId()]);
        }
        if (null !== ($nextLogEntry = $logEntryRepository->findNext($case, $logEntry))) {
            $urls['next'] = $this->generateUrl('case_log_entry_show', ['case' => $case->getId(), 'logEntry' => $nextLogEntry->getId()]);
        }

        return $this->render('case/log_entry_show.html.twig', [
            'case' => $case,
            'log_entry' => $logEntry,
            'data' => $logEntryHelper->getDisplayData($logEntry),
            'urls' => $urls,
        ]);
    }

    /**
     * @Route("/{id}/presentation", name="case_presentation", methods={"GET", "POST"})
     */
    public function presentation(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

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
            $this->addFlash('success', new TranslatableMessage('Case presentation updated', [], 'case'));

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
        $this->denyAccessUnlessGranted('edit', $case);

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
            $this->addFlash('success', new TranslatableMessage('Case decision proposal updated', [], 'case'));

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
        $this->denyAccessUnlessGranted('edit', $case);

        try {
            return $this->redirect($bbrHelper->getBBRMeddelelseUrlForCase($case, $addressProperty, $_format));
        } catch (\Exception $exception) {
            $this->addFlash('error', new TranslatableMessage('Cannot get url for BBR-Meddelelse', [], 'case'));
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
        $this->denyAccessUnlessGranted('edit', $case);

        try {
            $bbrHelper->updateCaseBBRData($case, $addressProperty);
            $entityManager->persist($case);
            $entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('BBR data updated', [], 'case'));
        } catch (\Exception $exception) {
            $this->addFlash('error', new TranslatableMessage('Cannot update BBR data ({message})', ['message' => $exception->getMessage()], 'case'));
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
        $this->denyAccessUnlessGranted('edit', $case);

        $rescheduleForm = $this->createForm(CaseRescheduleFinishProcessDeadlineType::class, $case);

        $rescheduleForm->handleRequest($request);

        if ($rescheduleForm->isSubmitted() && $rescheduleForm->isValid()) {
            $case->setHasReachedProcessingDeadline(false);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', new TranslatableMessage('Process deadline updated', [], 'case'));

            // Rendering a Twig template will consume the flash message, so for ajax requests we just send a JSON response.
            if ($request->get('ajax')) {
                return new JsonResponse(true);
            }

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_status', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_reschedule_finish_processing_deadline.html.twig', [
            'reschedule_form' => $rescheduleForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/reschedule-hearing-deadline", name="case_reschedule_finish_hearing_deadline", methods={"GET","POST"})
     */
    public function rescheduleFinishHearingDeadline(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $rescheduleForm = $this->createForm(CaseRescheduleFinishHearingDeadlineType::class, $case);

        $rescheduleForm->handleRequest($request);

        if ($rescheduleForm->isSubmitted() && $rescheduleForm->isValid()) {
            $case->setHasReachedHearingDeadline(false);

            if ($case->getFinishHearingDeadline() > $case->getFinishProcessingDeadline()) {
                // Ensure processing deadline is always after
                $case->setFinishProcessingDeadline($case->getFinishHearingDeadline());
                $case->setHasReachedProcessingDeadline(false);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', new TranslatableMessage('Hearing deadline updated', [], 'case'));

            // Rendering a Twig template will consume the flash message, so for ajax requests we just send a JSON response.
            if ($request->get('ajax')) {
                return new JsonResponse(true);
            }

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_status', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_reschedule_finish_hearing_deadline.html.twig', [
            'reschedule_form' => $rescheduleForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/assign-caseworker", name="case_assign_caseworker", methods={"POST"})
     */
    public function assignCaseworker(CaseEntity $case, UserRepository $userRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $currentCaseWorker = $case->getAssignedTo();

        $availableCaseworkers = $userRepository->findByRole('ROLE_CASEWORKER', ['name' => 'ASC']);

        $assignForm = $this->createForm(CaseAssignCaseworkerType::class, $case, ['available_caseworkers' => $availableCaseworkers]);

        $assignForm->handleRequest($request);

        if ($assignForm->isSubmitted() && $assignForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Add flash based on which action is happening
            if ($case->getAssignedTo() && $currentCaseWorker instanceof User) {
                // Case is reassigned
                $this->addFlash('success', new TranslatableMessage('Reassigned case {case_number} from {old_case_worker_name} to {new_case_worker_name}', [
                    'old_case_worker_name' => $currentCaseWorker->getName(),
                    'new_case_worker_name' => $case->getAssignedTo()->getName(),
                    'case_number' => $case->getCaseNumber(),
                ], 'case'));
            } elseif ($case->getAssignedTo() && is_null($currentCaseWorker)) {
                // Case is assigned
                $this->addFlash('success', new TranslatableMessage('Case worker {name} assigned to case {case_number}', [
                    'name' => $case->getAssignedTo()->getName(),
                    'case_number' => $case->getCaseNumber(),
                ], 'case'));
            } else {
                // Assignee is removed from case
                $this->addFlash('success', new TranslatableMessage('Removed assigned case worker {name} from case {case_number}', [
                    'name' => $currentCaseWorker->getName(),
                    'case_number' => $case->getCaseNumber(),
                ], 'case'));
            }

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_index');

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_assign_caseworker.html.twig', [
            'assign_form' => $assignForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/validate-address/{addressProperty}", name="case_validate_address", methods={"GET", "POST"})
     */
    public function validateAddress(Request $request, CaseEntity $case, AddressHelper $addressHelper, string $addressProperty, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $form = $this->createformbuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $addressHelper->validateCaseAddress($case, $addressProperty);
                $this->addFlash('success', new TranslatableMessage('Address validated', [], 'case'));

                // Rendering a Twig template will consume the flash message, so for ajax requests we just send a JSON response.
                if ($request->get('ajax')) {
                    return new JsonResponse(true);
                }

                // Send user back to where he came from.
                $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

                return $this->redirect($redirectUrl);
            } catch (\Exception $exception) {
                if ($request->get('ajax')) {
                    $form->addError(new FormError($translator->trans('Invalid address ({message})', ['message' => $exception->getMessage()], 'case')));
                } else {
                    $this->addFlash('error', new TranslatableMessage('Error validating address ({message})', ['message' => $exception->getMessage()], 'case'));
                }
            }
        }

        return $this->render('case/_validate_address.html.twig', [
            'form' => $form->createView(),
            'case' => $case,
            'address_property' => $addressProperty,
        ]);
    }

    /**
     * @Route("/{id}/move_case", name="case_move", methods={"POST"})
     */
    public function move(BoardRepository $boardRepository, CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $availableBoards = $boardRepository->findDifferentSuitableBoards($case->getBoard());

        $moveForm = $this->createForm(CaseMoveType::class, $case, ['boards' => $availableBoards]);

        $moveForm->handleRequest($request);

        if ($moveForm->isSubmitted() && $moveForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', new TranslatableMessage('Case moved', [], 'case'));

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_summary', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_move_case.html.twig', [
            'move_form' => $moveForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="case_delete", methods={"POST"})
     */
    public function delete(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $case);

        if (!$this->isDeletable($case)) {
            $message = 'Attempted to delete non-deletable case.';
            throw new BadRequestException($message);
        }

        $deleteForm = $this->createForm(CaseDeleteType::class, $case);

        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $case->setSoftDeleted(true);
            $now = new \DateTime('now');
            $case->setSoftDeletedAt($now);

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', new TranslatableMessage('Case deleted', [], 'case'));

            $redirectUrl = $this->generateUrl('default', ['id' => $case->getId()]);

            return $this->redirect($redirectUrl);
        }

        return $this->render('case/_delete.html.twig', [
            'delete_form' => $deleteForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * Checks if case can be deleted.
     */
    private function isDeletable(CaseEntity $case): bool
    {
        // If it is in hearing or has been in hearing it is not deletable
        $hasBeenInHearing = $case->getHearing() && ($case->getHearing()->getStartedOn() || $case->getHearing()->getFinishedOn());
        // If it has been on agenda or is on agenda it is also not deletable
        return $case->getAgendaCaseItems()->isEmpty() && !$hasBeenInHearing;
    }

    /**
     * @Route("/{id}/validate-identifier/{idProperty}/{addressProperty}/{nameProperty}", name="case_validate_identifier", methods={"GET", "POST"})
     */
    public function validateIdentifier(Request $request, CaseEntity $case, IdentificationHelper $identificationHelper, string $idProperty, string $addressProperty, string $nameProperty, TranslatorInterface $translator, PropertyAccessorInterface $propertyAccessor): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $form = $this->createformbuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $identificationHelper->validateIdentification($case, $idProperty, $addressProperty, $nameProperty);

                $this->addFlash('success', new TranslatableMessage('Identification validated', [], 'case'));

                // Rendering a Twig template will consume the flash message, so for ajax requests we just send a JSON response.
                if ($request->get('ajax')) {
                    return new JsonResponse(true);
                }

                // Send user back to where he came from.
                $redirectUrl = $request->query->get('referer') ?? $this->generateUrl('case_show', ['id' => $case->getId()]);

                return $this->redirect($redirectUrl);
            } catch (\Exception $exception) {
                if ($request->get('ajax')) {
                    $form->addError(new FormError($translator->trans('Invalid identifier ({message})', ['message' => $exception->getMessage()], 'case')));
                } else {
                    $this->addFlash('error', new TranslatableMessage('Error validating identifier ({message})', ['message' => $exception->getMessage()], 'case'));
                }
            }
        }

        return $this->render('case/_validate_identifier.html.twig', [
            'form' => $form->createView(),
            'case' => $case,
            'id_property' => $idProperty,
            'address_property' => $addressProperty,
            'name_property' => $nameProperty,
        ]);
    }

    /**
     * @Route("/new/apply-identifier-data", name="case_new_apply_identifier_data", methods={"GET", "POST"})
     */
    public function applyIdentifierData(IdentificationHelper $identificationHelper, Request $request): Response
    {
        $type = $request->request->get('type');
        $identifier = $request->request->get('identifier');

        return $identificationHelper->fetchIdentifierData($identifier, $type);
    }
}
