<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\CaseDecisionProposal;
use App\Entity\CasePresentation;
use App\Entity\Document;
use App\Form\CaseDecisionProposalType;
use App\Form\CasePresentationType;
use App\Form\InspectionLetterType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\DocumentRepository;
use App\Service\AgendaHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda/{id}/item/{agenda_item_id}")
 */
class AgendaCaseItemController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var AgendaHelper
     */
    private $agendaHelper;

    public function __construct(AgendaHelper $agendaHelper, EntityManagerInterface $entityManager)
    {
        $this->agendaHelper = $agendaHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/inspection", name="agenda_case_item_inspection", methods={"GET"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function inspection(Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        return $this->render('agenda_case_item/inspection.html.twig', [
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/inspection-letter", name="agenda_case_item_inspection_letter", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function inspectionLetter(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
    {
        $form = $this->createForm(InspectionLetterType::class);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            //TODO: Add logic for sending letter

            return $this->redirectToRoute('agenda_case_item_inspection_letter', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_case_item/inspection_letter.html.twig', [
            'inspection_letter_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/presentation", name="agenda_case_item_presentation", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function presentation(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
    {
        $casePresentation = $agendaItem->getPresentation() ?? new CasePresentation();

        $agendaOptions = $this->agendaHelper->getFormOptionsForAgenda($agenda);

        $form = $this->createForm(CasePresentationType::class, $casePresentation, $agendaOptions);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            /** @var CasePresentation $casePresentation */
            $casePresentation = $form->getData();

            // TODO: possibly save this on the case in form of a document?
            // Should this be done when agenda is published?
            $agendaItem->setPresentation($casePresentation);

            $this->entityManager->persist($casePresentation);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_case_item_presentation', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_case_item/presentation.html.twig', [
            'case_presentation_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/decision-proposal", name="agenda_case_item_decision_proposal", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function decisionProposal(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
    {
        $decisionProposal = $agendaItem->getDecisionProposal() ?? new CaseDecisionProposal();

        $agendaOptions = $this->agendaHelper->getFormOptionsForAgenda($agenda);

        $form = $this->createForm(CaseDecisionProposalType::class, $decisionProposal, $agendaOptions);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            $decisionProposal = $form->getData();

            // TODO: possibly save this on the case in form of a document?
            // Should this be done when agenda is published?
            $agendaItem->setDecisionProposal($decisionProposal);

            $this->entityManager->persist($decisionProposal);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_case_item_decision_proposal', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_case_item/decision_proposal.html.twig', [
            'decision_proposal_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/case/documents", name="agenda_case_item_document", methods={"GET", "POST"})
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function caseItemDocuments(Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        $documents = $agendaItem->getDocuments();

        return $this->render('agenda_case_item/documents.html.twig', [
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/case/documents/select", name="agenda_case_item_document_attach", methods={"GET", "POST"})
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function selectDocuments(Agenda $agenda, AgendaCaseItem $agendaItem, CaseDocumentRelationRepository $relationRepository, DocumentRepository $documentRepository, Request $request): Response
    {
        $case = $agendaItem->getCaseEntity();

        $caseDocuments = $relationRepository->findNonDeletedDocumentsByCase($case);
        $agendaItemDocuments = $agendaItem->getDocuments()->toArray();

        $availableDocuments = array_diff($caseDocuments, $agendaItemDocuments);

        if ($request->isMethod('GET') || $agenda->isFinished()) {
            return $this->render('agenda_case_item/documents_attach.html.twig', [
                'agenda' => $agenda,
                'agenda_item' => $agendaItem,
                'documents' => $availableDocuments,
            ]);
        }

        $documentIds = $request->request->get('documents');

        if (null !== $documentIds) {
            $documents = $documentRepository->findMany($documentIds);

            foreach ($documents as $document) {
                $agendaItem->addDocument($document);
            }

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_case_item_document', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }

    /**
     * @Route("/case/documents/delete/{document_id}", name="agenda_case_item_document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function caseAgendaDocumentDelete(Agenda $agenda, AgendaCaseItem $agendaItem, Document $document, Request $request): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            $agendaItem->removeDocument($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_case_item_document', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }
}
