<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Form\AgendaItemType;
use App\Service\AgendaItemHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda/{id}/item")
 */
class AgendaItemController extends AbstractController
{
    /**
     * @var AgendaItemHelper
     */
    private $agendaItemHelper;

    public function __construct(AgendaItemHelper $agendaItemHelper)
    {
        $this->agendaItemHelper = $agendaItemHelper;
    }

    /**
     * @Route("/create", name="agenda_item_create", methods={"GET", "POST"})
     */
    public function create(Agenda $agenda, Request $request): Response
    {
        $form = $this->createForm(AgendaItemType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newAgendaItem = $this->agendaItemHelper->handleAgendaItemForm($form);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newAgendaItem);
            $entityManager->flush();

            return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
        }

        return $this->render('agenda_item/new.html.twig', [
            'agenda_item_create_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }
}
