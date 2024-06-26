<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaManuelItem;
use App\Entity\Document;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use App\Form\DocumentType;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @Route("/agenda/{id}/item/{agenda_item_id}/documents")
 */
class AgendaManuelItemDocumentController extends AbstractController
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
     * @Route("", name="agenda_manuel_item_documents", methods={"GET"})
     *
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     */
    public function index(Agenda $agenda, AgendaManuelItem $agendaItem): Response
    {
        $this->denyAccessUnlessGranted('view', $agendaItem);

        $documents = $agendaItem->getDocuments();

        return $this->render('agenda_manuel_item/documents.html.twig', [
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/upload", name="agenda_manuel_item_upload_document", methods={"GET", "POST"})
     *
     * @Entity("agenda", expr="repository.find(id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws DocumentDirectoryException
     * @throws FileMovingException
     */
    public function upload(Agenda $agenda, AgendaManuelItem $agendaItem, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $isFinishedAgenda = $agenda->isFinished();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && !$isFinishedAgenda) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $documentName = $document->getDocumentName();
            $documentType = $document->getType();
            /** @var UploadedFile[] $files */
            $files = $form->get('files')->getData();

            foreach ($files as $file) {
                $newDocument = $this->documentUploader->createDocumentFromUploadedFile($file, $documentName, $documentType);

                $agendaItem->addDocument($newDocument);

                $this->entityManager->persist($newDocument);
            }
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('{count, plural, =1 {One document uploaded} other {# documents uploaded}}', ['count' => count($files)], 'agenda'));

            return $this->redirectToRoute('agenda_manuel_item_documents', [
                'id' => $agenda->getId(),
                'agenda_item_id' => $agendaItem->getId(),
            ]);
        }

        return $this->render('agenda_manuel_item/document_upload.html.twig', [
            'document_form' => $form->createView(),
            'agenda' => $agenda,
            'agenda_item' => $agendaItem,
        ]);
    }

    /**
     * @Route("/view/{document_id}", name="agenda_manuel_item_document_view", methods={"GET", "POST"})
     *
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     *
     * @throws DocumentDirectoryException
     */
    public function view(AgendaManuelItem $agendaItem, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('view', $agendaItem);

        $response = $uploader->handleViewDocument($document);

        return $response;
    }

    /**
     * @Route("/delete/{document_id}", name="agenda_manuel_item_document_delete", methods={"DELETE"})
     *
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("agendaItem", expr="repository.find(agenda_item_id)")
     * @Entity("agenda", expr="repository.find(id)")
     */
    public function delete(Agenda $agenda, AgendaManuelItem $agendaItem, Document $document, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $agendaItem);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token')) && !$agenda->isFinished()) {
            $agendaItem->removeDocument($document);
            $this->entityManager->remove($document);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Document removed', [], 'agenda'));
        }

        return $this->redirectToRoute('agenda_manuel_item_documents', [
            'id' => $agenda->getId(),
            'agenda_item_id' => $agendaItem->getId(),
        ]);
    }
}
