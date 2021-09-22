<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\User;
use App\Form\AgendaAddBoardMemberType;
use App\Form\AgendaCreateType;
use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\MunicipalityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpCsFixer\Console\Report\FixReport\ReportSummary;
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
     */
    public function show(Agenda $agenda, Request $request): Response
    {

        $boardMembers = $agenda->getBoardmembers();

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
        ] );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $addedBoardMembers = $form->get('boardMemberToAdd')->getData();

            foreach ($addedBoardMembers as $addedBoardMember){
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
}
