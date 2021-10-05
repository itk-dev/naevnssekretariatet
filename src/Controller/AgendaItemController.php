<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaCaseItem;
use App\Entity\AgendaItem;
use App\Entity\AgendaManuelItem;
use App\Entity\CaseDecisionProposal;
use App\Entity\CasePresentation;
use App\Entity\Document;
use App\Entity\User;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use App\Form\AgendaItemType;
use App\Form\CaseDecisionProposalType;
use App\Form\CasePresentationType;
use App\Form\DocumentForm;
use App\Form\InspectionLetterType;
use App\Repository\CaseDocumentRelationRepository;
use App\Repository\DocumentRepository;
use App\Service\AgendaItemHelper;
use App\Service\DocumentUploader;
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
    /**
     * @var DocumentUploader
     */
    private $documentUploader;

    public function __construct(AgendaItemHelper $agendaItemHelper, DocumentUploader $documentUploader, EntityManagerInterface $entityManager)
    {
        $this->agendaItemHelper = $agendaItemHelper;
        $this->documentUploader = $documentUploader;
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

        $twigLayout = 'layout-with-agenda-manuel-item-submenu.html.twig';

        if (AgendaCaseItem::class === get_class($agendaItem)) {
            $options['relevantCase'] = $agendaItem->getCaseEntity();
            $twigLayout = 'layout-with-agenda-case-item-submenu.html.twig';
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
            'layout' => $twigLayout,
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

    /**
     * @Route("/{agenda_item_id}/documents", name="agenda_item_manuel_documents", methods={"GET"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function document(Agenda $agenda, AgendaManuelItem $agendaItem): Response
    {
        $documents = $agendaItem->getDocuments();

        return $this->render('agenda_item/manuel_item_documents.html.twig', [
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/documents/upload", name="agenda_item_upload_document", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws DocumentDirectoryException
     * @throws FileMovingException
     */
    public function uploadDocument(Request $request, Agenda $agenda, AgendaManuelItem $agendaItem): Response
    {
        $this->documentUploader->specifyDirectory('/agenda_item_documents/');

        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentForm::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $file = $form->get('filename')->getData();

            $newFilename = $this->documentUploader->upload($file);

            // Set filename, document name and creator/uploader
            $document->setFilename($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setUploadedBy($uploader);

            $agendaItem->addDocument($document);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return $this->redirectToRoute('agenda_item_manuel_documents', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_item/manuel_item_document_upload.html.twig', [
            'document_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/documents/download/{document_id}", name="agenda_item_manuel_document_download", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     *
     * @throws DocumentDirectoryException
     */
    public function download(Document $document, DocumentUploader $uploader): Response
    {
        $uploader->specifyDirectory('/agenda_item_documents/');
        $response = $uploader->handleDownload($document);

        return $response;
    }

    /**
     * @Route("/{agenda_item_id}/documents/delete/{document_id}", name="agenda_item_manuel_document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function documentDelete(Request $request, Agenda $agenda, AgendaManuelItem $agendaItem, Document $document): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $agendaItem->removeDocument($document);
            $this->entityManager->remove($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_item_manuel_documents', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/case/documents", name="agenda_item_case_document", methods={"GET", "POST"})
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
     * @Route("/{agenda_item_id}/case/documents/select", name="agenda_item_case_document_attach", methods={"GET", "POST"})
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function selectDocuments(Request $request, Agenda $agenda, AgendaCaseItem $agendaItem, CaseDocumentRelationRepository $caseDocumentRelationRepository, DocumentRepository $documentRepository): Response
    {
        $case = $agendaItem->getCaseEntity();

        $caseDocuments = $caseDocumentRelationRepository->findNonDeletedDocumentsByCase($case);
        $agendaItemDocuments = $agendaItem->getDocuments()->toArray();

        $availableDocuments = array_diff($caseDocuments, $agendaItemDocuments);

        $query = [];

        // TODO: Adversary possibly write own query string and cause issues?
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

                return $this->redirectToRoute('agenda_item_case_document', [
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
     * @Route("/{agenda_item_id}/case/documents/delete/{document_id}", name="agenda_item_case_document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function casedAgendaDocumentDelete(Request $request, Agenda $agenda, AgendaCaseItem $agendaItem, Document $document): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $agendaItem->removeDocument($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_item_case_document', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }
}
