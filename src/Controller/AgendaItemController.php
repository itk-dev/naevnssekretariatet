<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\CaseDecisionProposal;
use App\Entity\CasePresentation;
use App\Form\AgendaItemType;
use App\Form\CaseDecisionProposalType;
use App\Form\CasePresentationType;
use App\Form\InspectionLetterType;
use App\Service\AgendaItemHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
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
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(AgendaItemHelper $agendaItemHelper, EntityManagerInterface $entityManager)
    {
        $this->agendaItemHelper = $agendaItemHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/create", name="agenda_item_create", methods={"GET", "POST"})
     */
    public function create(Agenda $agenda, Request $request): Response
    {
        $form = $this->createForm(AgendaItemType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $agendaItem = $form->get('agendaItem')->getData();
            $agenda->addAgendaItem($agendaItem);
            $this->entityManager->persist($agendaItem);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_show', ['id' => $agenda->getId()]);
        }

        return $this->render('agenda_item/new.html.twig', [
            'agenda_item_create_form' => $form->createView(),
            'agenda' => $agenda,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/edit", name="agenda_item_edit", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws Exception
     */
    public function edit(Request $request, Agenda $agenda, AgendaItem $agendaItem): Response
    {
        $formClass = $this->agendaItemHelper->getFormType($agendaItem);

        $options = [];

        $isManuelItem = true;

        if (AgendaCaseItem::class === get_class($agendaItem)) {
            $options['relevantCase'] = $agendaItem->getCaseEntity();
            $isManuelItem = false;
        }

        $form = $this->createForm($formClass, $agendaItem, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_item_edit', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_item/edit.html.twig', [
            'agenda_item_edit_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
            'isManuelItem' => $isManuelItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/inspection", name="agenda_item_inspection", methods={"GET"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function inspection(Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        return $this->render('agenda_item/inspection.html.twig', [
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/inspection-letter", name="agenda_item_inspection_letter", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function inspectionLetter(Request $request, Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        $form = $this->createForm(InspectionLetterType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_item_edit', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_item/inspection_letter.html.twig', [
            'inspection_letter_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/presentation", name="agenda_item_presentation", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function presentation(Request $request, Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        // We are guaranteed this to be an AgendaCaseItem

        if (null !== $agendaItem->getPresentation()) {
            $casePresentation = $agendaItem->getPresentation();
        } else {
            $casePresentation = new CasePresentation();
        }

        $form = $this->createForm(CasePresentationType::class, $casePresentation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $casePresentation = $form->getData();

            // TODO: possibly save this on the case in form of a document?
            // Should this be done when agenda is published?
            $agendaItem->setPresentation($casePresentation);

            $this->entityManager->persist($casePresentation);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_item_presentation', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_item/presentation.html.twig', [
            'case_presentation_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/decision-proposal", name="agenda_item_decision_proposal", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function decisionProposal(Request $request, Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        // We are guaranteed this to be an AgendaCaseItem

        if (null !== $agendaItem->getDecisionProposal()) {
            $decisionProposal = $agendaItem->getDecisionProposal();
        } else {
            $decisionProposal = new CaseDecisionProposal();
        }

        $form = $this->createForm(CaseDecisionProposalType::class, $decisionProposal);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $decisionProposal = $form->getData();

            // TODO: possibly save this on the case in form of a document?
            // Should this be done when agenda is published?
            $agendaItem->setDecisionProposal($decisionProposal);

            $this->entityManager->persist($decisionProposal);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_item_decision_proposal', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_item/decision_proposal.html.twig', [
            'decision_proposal_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}", name="agenda_item_delete", methods={"DELETE"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function delete(Request $request, Agenda $agenda, AgendaItem $agendaItem): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$agendaItem->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($agendaItem);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_show', [
            'agenda' => $agenda,
            'id' => $agenda->getId(),
        ]);
    }
}
