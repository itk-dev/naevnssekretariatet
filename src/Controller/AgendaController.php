<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaItem;
use App\Entity\BoardMember;
use App\Entity\User;
use App\Form\AgendaAddBoardMemberType;
use App\Form\AgendaCreateType;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\MunicipalityRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/agenda")
 */
class AgendaController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="agenda_index", methods={"GET"})
     */
    public function index(AgendaRepository $agendaRepository, MunicipalityRepository $municipalityRepository, Security $security): Response
    {
        // Get current User
        /** @var User $user */
        $user = $security->getUser();

        // Get favorite municipality
        // null is fine as it is only used for selecting an option
        $favoriteMunicipality = $user->getFavoriteMunicipality();

        // Get municipalities
        $municipalities = $municipalityRepository->findAll();

        $agendas = $agendaRepository->findAll();

        return $this->render('agenda/index.html.twig', [
            'municipalities' => $municipalities,
            'favorite_municipality' => $favoriteMunicipality,
            'agendas' => $agendas,
        ]);
    }

    /**
     * @Route("/create", name="agenda_create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $agenda = new Agenda();

        $form = $this->createForm(AgendaCreateType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $agenda = $form->getData();

            $this->entityManager->persist($agenda);
            $this->entityManager->flush();

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
    public function delete(Request $request, Agenda $agenda): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$agenda->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($agenda);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_index');
    }

    /**
     * @Route("/{id}/show", name="agenda_show", methods={"GET", "POST"})
     *
     * @throws Exception
     */
    public function show(Agenda $agenda, Request $request): Response
    {
        $boardMembers = $agenda->getBoardmembers();
        $agendaItems = $agenda->getAgendaItems()->toArray();

        if (!empty($agendaItems)) {
            usort($agendaItems, function (AgendaItem $a, AgendaItem $b) {
                $ad = new DateTime($a->getStartTime()->format('H:i'));
                $bd = new DateTime($b->getStartTime()->format('H:i'));

                if ($ad === $bd) {
                    return 0;
                }

                return $ad < $bd ? -1 : 1;
            });
        }

        $form = $this->createForm(AgendaType::class, $agenda);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $agenda = $form->getData();
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_show', [
                'id' => $agenda->getId(),
                'agenda' => $agenda,
            ]);
        }

        return $this->render('agenda/show.html.twig', [
            'agenda_form' => $form->createView(),
            'agenda' => $agenda,
            'boardMembers' => $boardMembers,
            'agendaItems' => $agendaItems,
        ]);
    }

    /**
     * @Route("/{id}/add-board-member", name="agenda_add_board_member", methods={"GET", "POST"})
     */
    public function addBoardMember(Agenda $agenda, Request $request): Response
    {
        $allBoardMembers = $agenda->getSubBoard()->getBoardMembers()->toArray();
        $currentBoardMembersOnAgenda = $agenda->getBoardmembers()->toArray();
        $choices = array_diff($allBoardMembers, $currentBoardMembersOnAgenda);

        $form = $this->createForm(AgendaAddBoardMemberType::class, [], [
            'board_member_choices' => $choices,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addedBoardMembers = $form->get('boardMemberToAdd')->getData();

            foreach ($addedBoardMembers as $addedBoardMember) {
                $agenda->addBoardmember($addedBoardMember);
            }

            $this->entityManager->flush();

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
    public function removeBoardMember(Request $request, BoardMember $boardMember, Agenda $agenda): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('remove'.$boardMember->getId(), $request->request->get('_token'))) {
            // Simply just soft delete by setting soft deleted to true

            $agenda->removeBoardmember($boardMember);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
    }

    /**
     * @Route("/{id}/inspection", name="agenda_inspection", methods={"GET"})
     */
    public function inspection(Agenda $agenda): Response
    {
        return $this->render('agenda/inspection.html.twig', [
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{id}/protocol", name="agenda_protocol", methods={"GET"})
     */
    public function protocol(Agenda $agenda): Response
    {
        return $this->render('agenda/protocol.html.twig', [
            'agenda' => $agenda,
        ]);
    }
}
