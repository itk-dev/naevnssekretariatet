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
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda/{id}/item")
 */
class AgendaCaseItemController extends AbstractController
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
     * @Route("/{agenda_item_id}/inspection", name="agenda_case_item_inspection", methods={"GET"})
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
     * @Route("/{agenda_item_id}/inspection-letter", name="agenda_case_item_inspection_letter", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function inspectionLetter(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
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
     * @Route("/{agenda_item_id}/presentation", name="agenda_case_item_presentation", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function presentation(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
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
            /** @var CasePresentation $casePresentation */
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
     * @Route("/{agenda_item_id}/decision-proposal", name="agenda_case_item_decision_proposal", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function decisionProposal(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
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
     * @Route("/{agenda_item_id}/case/documents", name="agenda_case_item_document", methods={"GET", "POST"})
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function caseItemDocuments(Agenda $agenda, AgendaCaseItem $agendaItem): Response
    {
        $documents = $agendaItem->getDocuments();

        return $this->render('agenda_item/case_item_documents.html.twig', [
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/case/documents/select", name="agenda_case_item_document_attach", methods={"GET", "POST"})
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function selectDocuments(Agenda $agenda, AgendaCaseItem $agendaItem, CaseDocumentRelationRepository $relationRepository, DocumentRepository $documentRepository, Request $request): Response
    {
        $case = $agendaItem->getCaseEntity();

        $caseDocuments = $relationRepository->findNonDeletedDocumentsByCase($case);
        $agendaItemDocuments = $agendaItem->getDocuments()->toArray();

        $availableDocuments = array_diff($caseDocuments, $agendaItemDocuments);

        $query = null;

        // TODO: Can adversary write own query string and cause issues?
        parse_str($request->getQueryString(), $query);

        if (!empty($query)) {
            // TODO: possibly try/catch
            $documentIds = $query['documents'];
            if (!empty($documentIds)) {
                foreach ($documentIds as $documentId) {
                    $documentToAdd = $documentRepository->findOneBy(['id' => $documentId]);
                    $agendaItem->addDocument($documentToAdd);
                }
                $this->entityManager->flush();

                return $this->redirectToRoute('agenda_case_item_document', [
                    'id' => $agenda->getId(),
                    'agenda_item_id' => $agendaItem->getId(),
                ]);
            }
        }

        return $this->render('agenda_item/case_item_attach_documents.html.twig', [
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
            'documents' => $availableDocuments,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/case/documents/delete/{document_id}", name="agenda_case_item_document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function caseAgendaDocumentDelete(Agenda $agenda, AgendaCaseItem $agendaItem, Document $document, Request $request): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $agendaItem->removeDocument($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_case_item_document', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }
}
