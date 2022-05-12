<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\Document;
use App\Exception\DocumentDirectoryException;
use App\Form\CaseDecisionProposalType;
use App\Form\CasePresentationType;
use App\Repository\DocumentRepository;
use App\Service\AgendaHelper;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

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
     * @Route("/presentation", name="agenda_case_item_presentation", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function presentation(Agenda $agenda, AgendaCaseItem $agendaItem, Request $request): Response
    {
        // TODO: When they wish to make case presentations in TVIST1 ensure permissions are ok
        $this->denyAccessUnlessGranted('view', $agendaItem);

        $casePresentation = $agendaItem->getCaseEntity()->getPresentation();

        $form = $this->createForm(CasePresentationType::class, $casePresentation, ['disabled' => true]);

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
        // TODO: When they wish to make case presentations in TVIST1 ensure permissions are ok
        $this->denyAccessUnlessGranted('view', $agendaItem);

        $decisionProposal = $agendaItem->getCaseEntity()->getDecisionProposal();

        $form = $this->createForm(CaseDecisionProposalType::class, $decisionProposal, ['disabled' => true]);

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
        $this->denyAccessUnlessGranted('view', $agendaItem);

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
    public function selectDocuments(Agenda $agenda, AgendaCaseItem $agendaItem, DocumentRepository $documentRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        $availableDocuments = $documentRepository->getAvailableDocumentsForAgendaItem($agendaItem);

        if ($request->isMethod('GET') || $agenda->isFinished()) {
            return $this->render('agenda_case_item/documents_attach.html.twig', [
                'agenda' => $agenda,
                'agenda_item' => $agendaItem,
                'documents' => $availableDocuments,
            ]);
        }

        $documentIds = $request->request->get('documents');

        if (null !== $documentIds) {
            // @todo DocumentRepository::findMany expects array
            $documents = $documentRepository->findMany($documentIds);

            foreach ($documents as $document) {
                $agendaItem->addDocument($document);
            }

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Documents added to agenda', [], 'agenda'));
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
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            $agendaItem->removeDocument($document);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Document removed from agenda', [], 'agenda'));
        }

        return $this->redirectToRoute('agenda_case_item_document', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }

    /**
     * @Route("/view/{document_id}", name="agenda_case_item_document_view", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws DocumentDirectoryException
     */
    public function view(AgendaCaseItem $agendaItem, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('view', $agendaItem);

        $response = $uploader->handleViewDocument($document);

        return $response;
    }
}
