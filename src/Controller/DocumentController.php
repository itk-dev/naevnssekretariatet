<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\User;
use App\Exception\DocumentDirectoryException;
use App\Exception\FileMovingException;
use App\Form\CopyDocumentForm;
use App\Form\DocumentType;
use App\Repository\CaseDocumentRelationRepository;
use App\Service\DocumentCopyHelper;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/case/{id}/documents")
 */
class DocumentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var DocumentCopyHelper
     */
    private $copyHelper;
    /**
     * @var DocumentUploader
     */
    private $documentUploader;

    public function __construct(EntityManagerInterface $entityManager, DocumentCopyHelper $copyHelper, DocumentUploader $documentUploader)
    {
        $this->entityManager = $entityManager;
        $this->copyHelper = $copyHelper;
        $this->documentUploader = $documentUploader;
    }

    /**
     * @Route("/", name="document_index", methods={"GET"})
     */
    public function index(CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $this->denyAccessUnlessGranted('employee', $case);

        $nonDeletedDocuments = $relationRepository->findNonDeletedDocumentsByCase($case);

        return $this->render('documents/index.html.twig', [
            'case' => $case,
            'documents' => $nonDeletedDocuments,
        ]);
    }

    /**
     * @Route("/create", name="document_create", methods={"GET", "POST"})
     *
     * @throws FileMovingException
     * @throws DocumentDirectoryException
     */
    public function create(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $this->documentUploader->specifyDirectory('/case_documents/');

        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $file = $form->get('filename')->getData();

            $newFilename = $this->documentUploader->upload($file);

            // Set filename, document name, creator and case
            $document->setFilename($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setUploadedBy($uploader);

            $relation = new CaseDocumentRelation();
            $relation->setCase($case);
            $relation->setDocument($document);

            $this->entityManager->persist($document);
            $this->entityManager->persist($relation);
            $this->entityManager->flush();

            return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
        }

        return $this->render('documents/create.html.twig', [
            'case' => $case,
            'document_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{document_id}", name="document_delete", methods={"DELETE"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function delete(Request $request, Document $document, CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            // Simply just soft delete by setting soft deleted to true

            $relation = $relationRepository->findOneBy(['case' => $case, 'document' => $document]);
            $relation->setSoftDeleted(true);
            $dateTime = new \DateTime('NOW');
            $relation->setSoftDeletedAt($dateTime);

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
    }

    /**
     * @Route("/copy/{document_id}", name="document_copy", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function copy(Request $request, Document $document, CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        // Find suitable cases
        $suitableCases = $this->copyHelper->findSuitableCases($case, $document);

        $form = $this->createForm(CopyDocumentForm::class, null, ['case' => $case, 'suitableCases' => $suitableCases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cases = $form->get('cases')->getData();

            $this->copyHelper->handleCopyForm($cases, $document);

            return $this->redirectToRoute('document_index', ['id' => $case->getId()]);
        }

        return $this->render('documents/copy.html.twig', [
            'copy_document_form' => $form->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/download/{document_id}", name="document_download", methods={"GET", "POST"})
     * @Entity("document", expr="repository.find(document_id)")
     * @Entity("case", expr="repository.find(id)")
     *
     * @throws DocumentDirectoryException
     */
    public function download(CaseEntity $case, Document $document, DocumentUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted('employee', $case);

        $uploader->specifyDirectory('/case_documents/');
        $response = $uploader->handleDownload($document);

        return $response;
    }
}
