<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaManuelItem;
use App\Entity\Document;
use App\Entity\User;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use App\Form\DocumentForm;
use App\Service\AgendaHelper;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/agenda/{id}/item")
 */
class AgendaManuelItemController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var DocumentUploader
     */
    private $documentUploader;

    public function __construct(DocumentUploader $documentUploader, EntityManagerInterface $entityManager)
    {
        $this->documentUploader = $documentUploader;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/{agenda_item_id}/documents", name="agenda_manuel_item_documents", methods={"GET"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function document(Agenda $agenda, AgendaHelper $agendaHelper, AgendaManuelItem $agendaItem): Response
    {
        $documents = $agendaItem->getDocuments();

        return $this->render('agenda_manuel_item/documents.html.twig', [
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
            'documents' => $documents,
            'isFinishedAgenda' => $agendaHelper->isFinishedAgenda($agenda),
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/documents/upload", name="agenda_manuel_item_upload_document", methods={"GET", "POST"})
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws DocumentDirectoryException
     * @throws FileMovingException
     */
    public function uploadDocument(Agenda $agenda, AgendaManuelItem $agendaItem, Request $request): Response
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

            return $this->redirectToRoute('agenda_manuel_item_documents', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_manuel_item/document_upload.html.twig', [
            'document_form' => $form->createView(),
            'agenda' => $agenda,
            'agendaItem' => $agendaItem,
        ]);
    }

    /**
     * @Route("/{agenda_item_id}/documents/download/{document_id}", name="agenda_manuel_item_document_download", methods={"GET", "POST"})
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
     * @Route("/{agenda_item_id}/documents/delete/{document_id}", name="agenda_manuel_item_document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function documentDelete(Agenda $agenda, AgendaManuelItem $agendaItem, Document $document, Request $request): Response
    {
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $agendaItem->removeDocument($document);
            $this->entityManager->remove($document);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('agenda_manuel_item_documents', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }
}
