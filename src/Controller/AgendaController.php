<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaProtocol;
use App\Entity\BoardMember;
use App\Entity\User;
use App\Exception\BoardMemberException;
use App\Form\AgendaAddBoardMemberType;
use App\Form\AgendaEditType;
use App\Form\AgendaFilterType;
use App\Form\AgendaNewType;
use App\Form\AgendaProtocolType;
use App\Form\MunicipalitySelectorType;
use App\Repository\AgendaItemRepository;
use App\Repository\AgendaRepository;
use App\Repository\BoardMemberRepository;
use App\Repository\MunicipalityRepository;
use App\Service\AgendaHelper;
use App\Service\AgendaStatus;
use App\Service\MunicipalityHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\UuidV4;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/agenda")
 */
class AgendaController extends AbstractController
{
    public function __construct(private AgendaHelper $agendaHelper, private EntityManagerInterface $entityManager, private TranslatorInterface $translator)
    {
    }

    /**
     * @Route("/", name="agenda_index", methods={"GET", "POST"})
     */
    public function index(AgendaRepository $agendaRepository, PaginatorInterface $paginator, FilterBuilderUpdaterInterface $filterBuilderUpdater, MunicipalityHelper $municipalityHelper, MunicipalityRepository $municipalityRepository, Request $request): Response
    {
        $activeMunicipality = $municipalityHelper->getActiveMunicipality();

        $municipalities = $municipalityRepository->findAll();

        $municipalityForm = $this->createForm(MunicipalitySelectorType::class, null, [
            'municipalities' => $municipalities,
            'active_municipality' => $activeMunicipality,
        ]);

        $municipalityForm->handleRequest($request);
        if ($municipalityForm->isSubmitted()) {
            $municipality = $municipalityForm->get('municipality')->getData();

            $municipalityHelper->setActiveMunicipalitySession($municipality);

            return $this->redirectToRoute('agenda_index');
        }

        // Setup filter and pagination
        // If user is a board member we have to modify list of agendas and filters shown
        if ($this->isGranted('ROLE_BOARD_MEMBER')) {
            /** @var User $user */
            $user = $this->getUser();

            $boardMember = $user->getBoardMember();

            if (null === $boardMember) {
                $message = sprintf('User %s is not linked to any board member.', $user->getName());
                throw new BoardMemberException($message);
            }

            $filterBuilder = $agendaRepository->createQueryBuilderForBoardMember($boardMember);

            $filterOptions = [
                'municipality' => $activeMunicipality,
                'isBoardMember' => true,
            ];
        } else {
            $filterBuilder = $agendaRepository->createQueryBuilder('a');

            $filterOptions = [
                'municipality' => $activeMunicipality,
                'isBoardMember' => false,
            ];
        }

        $filterForm = $this->createForm(AgendaFilterType::class, null, $filterOptions);

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
        } else {
            // Default filter
            $filterForm->submit([
                'board' => '',
                'date' => '',
                'status' => AgendaStatus::NOT_FINISHED,
            ]);
        }
        $filterBuilderUpdater->addFilterConditions($filterForm, $filterBuilder);

        // Add sortable fields.
        $filterBuilder->leftJoin('a.board', 'board');
        $filterBuilder->addSelect('partial board.{id,name}');

        // Only get agendas under active municipality
        $filterBuilder->andWhere('board.municipality = :municipality')
            ->setParameter('municipality', $activeMunicipality->getId()->toBinary())
        ;

        $query = $filterBuilder->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            [
                'defaultSortFieldName' => 'a.date',
                'defaultSortDirection' => 'ASC',
            ]
        );

        $pagination->setCustomParameters(['align' => 'center']);

        return $this->render('agenda/index.html.twig', [
            'filter_form' => $filterForm->createView(),
            'municipalities' => $municipalities,
            'pagination' => $pagination,
            'municipality_form' => $municipalityForm->createView(),
        ]);
    }

    /**
     * @Route("/create", name="agenda_create", methods={"GET", "POST"})
     */
    public function create(MunicipalityHelper $municipalityHelper, Request $request): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        $agenda = new Agenda();

        $form = $this->createForm(AgendaNewType::class, $agenda, [
            'municipality' => $municipalityHelper->getActiveMunicipality(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Agenda $agenda */
            $agenda = $form->getData();

            $this->entityManager->persist($agenda);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Agenda created', [], 'agenda'));

            return $this->redirectToRoute('agenda_show', [
                'agenda' => $agenda,
                'id' => $agenda->getId(),
            ]);
        }

        return $this->render('agenda/create.html.twig', [
            'agenda_create_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="agenda_delete", methods={"DELETE"})
     */
    public function delete(Agenda $agenda, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $agenda);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$agenda->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            $this->entityManager->remove($agenda);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Agenda deleted', [], 'agenda'));
        }

        return $this->redirectToRoute('agenda_index');
    }

    /**
     * @Route("/{id}/show", name="agenda_show", methods={"GET", "POST"})
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function show(Agenda $agenda, AgendaItemRepository $agendaItemRepository, BoardMemberRepository $memberRepository, Request $request): Response
    {
        $memberTriplesWithBinaryId = $memberRepository->getMembersAndRolesByAgenda($agenda);

        // Convert id into UuidV4
        $memberTriplesWithUuid = array_map(function ($memberTriple) {
            $uuid = UuidV4::fromString($memberTriple['id']);
            $memberTriple['id'] = $uuid->__toString();

            return $memberTriple;
        }, $memberTriplesWithBinaryId);

        // Sort according to name
        usort($memberTriplesWithUuid, static fn ($a, $b) => $a['name'] <=> $b['name']);

        $sortedAgendaItems = $agendaItemRepository->findAscendingAgendaItemsByAgenda($agenda);

        $agendaOptions = ($agenda->isFinished() || $this->isGranted('ROLE_BOARD_MEMBER')) ? ['disabled' => true] : [];

        $form = $this->createForm(AgendaEditType::class, $agenda, $agendaOptions);

        return $this->render('agenda/show.html.twig', [
            'agenda_form' => $form->createView(),
            'agenda' => $agenda,
            'board_member_triple' => $memberTriplesWithUuid,
            'agenda_items' => $sortedAgendaItems,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="agenda_edit", methods={"POST"})
     */
    public function edit(Agenda $agenda, Request $request): ?Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $agendaOptions = $agenda->isFinished() ? ['disabled' => true] : [];

        $form = $this->createForm(AgendaEditType::class, $agenda, $agendaOptions);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            $agenda = $form->getData();
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Agenda updated', [], 'agenda'));

            return $this->redirectToRoute('agenda_show', [
                'id' => $agenda->getId(),
                'agenda' => $agenda,
            ]);
        }

        return $this->redirectToRoute('agenda_show', [
            'id' => $agenda->getId(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{id}/add-board-member", name="agenda_add_board_member", methods={"GET", "POST"})
     */
    public function addBoardMember(Agenda $agenda, BoardMemberRepository $memberRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $availableBoardMembers = $memberRepository->getAvailableBoardMembersByAgenda($agenda);

        $form = $this->createForm(AgendaAddBoardMemberType::class, [], [
            'board_member_choices' => $availableBoardMembers,
            'board' => $agenda->getBoard(),
        ]);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            $addedBoardMembers = $form->get('boardMemberToAdd')->getData();

            foreach ($addedBoardMembers as $addedBoardMember) {
                $agenda->addBoardmember($addedBoardMember);
            }

            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Board members added to agenda', [], 'agenda'));

            return $this->redirectToRoute('agenda_show', [
                'agenda' => $agenda,
                'id' => $agenda->getId(),
            ]);
        }

        return $this->render('agenda/add_board_member.html.twig', [
            'agenda_add_board_member_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{id}/show/{board_member_id}", name="agenda_board_member_remove", methods={"DELETE"})
     * @Entity("boardMember", expr="repository.find(board_member_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function removeBoardMember(Agenda $agenda, BoardMember $boardMember, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('remove'.$boardMember->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            $agenda->removeBoardmember($boardMember);
            $this->entityManager->flush();
            $this->addFlash('success', $this->translator->trans('Board member %name% removed from agenda', ['name' => $boardMember->getName()], 'agenda'));
        }

        return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
    }

    /**
     * @Route("/{id}/protocol", name="agenda_protocol", methods={"GET", "POST"})
     */
    public function protocol(Agenda $agenda, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $isNewAgendaProtocol = false;
        $agendaProtocol = $agenda->getProtocol();
        if (null === $agendaProtocol) {
            $agendaProtocol = new AgendaProtocol();
            $isNewAgendaProtocol = true;
        }

        $agendaOptions = $this->agendaHelper->getFormOptionsForAgenda($agenda);

        $form = $this->createForm(AgendaProtocolType::class, $agendaProtocol, $agendaOptions);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            /** @var AgendaProtocol $agendaProtocol */
            $agendaProtocol = $form->getData();

            // Should this be done when agenda is published?
            $agenda->setProtocol($agendaProtocol);

            $this->entityManager->persist($agendaProtocol);
            $this->entityManager->flush();
            $this->addFlash('success', $isNewAgendaProtocol
                ? $this->translator->trans('Agenda protocol created', [], 'agenda')
                : $this->translator->trans('Agenda protocol updated', [], 'agenda')
            );

            return $this->redirectToRoute('agenda_protocol', [
                'id' => $agenda->getId(),
            ]);
        }

        return $this->render('agenda/protocol.html.twig', [
            'protocol_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{id}/publish", name="agenda_publish", methods={"GET", "POST"})
     */
    public function publishAgenda(Agenda $agenda): Response
    {
        $this->denyAccessUnlessGranted('edit', $agenda);

        $agenda->setIsPublished(true);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator->trans('Agenda published', [], 'agenda'));

        return $this->redirectToRoute('agenda_broadcast', [
            'id' => $agenda->getId(),
        ]);
    }
}
