<?php

namespace App\Controller;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\User;
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

    public function __construct(EntityManagerInterface $entityManager, DocumentCopyHelper $copyHelper)
    {
        $this->entityManager = $entityManager;
        $this->copyHelper = $copyHelper;
    }

    /**
     * @Route("/", name="document_index", methods={"GET"})
     */
    public function index(CaseEntity $case, CaseDocumentRelationRepository $relationRepository): Response
    {
        $nonDeletedDocuments = $relationRepository->findNonDeletedDocuments($case);

        return $this->render('documents/index.html.twig', [
            'case' => $case,
            'documents' => $nonDeletedDocuments,
        ]);
    }

    /**
     * @Route("/create", name="document_create", methods={"GET", "POST"})
     *
     * @throws FileMovingException
     */
    public function create(CaseEntity $case, Request $request, DocumentUploader $uploader): Response
    {
        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $file = $form->get('filename')->getData();

            $newFilename = $uploader->upload($file);

            // Set filename, document name, creator and case
            $document->setFilename($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setUploadedBy($uploader->getEmail());

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
        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            // Simply just soft delete by setting soft deleted to true

            $relation = $relationRepository->findOneBy(['case' => $case, 'document' => $document]);
            $relation->setSoftDeleted(true);

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
        // Find suitable cases
        $suitableCases = $this->copyHelper->findSuitableCases($case, $document);

        $form = $this->createForm(CopyDocumentForm::class, null, ['case' => $case, 'suitableCases' => $suitableCases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cases = $form->get('cases')->getData();

            $this->copyHelper->handleCopyForm($cases, $document, $relationRepository);

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
     */
    public function download(Document $document, CaseEntity $case, DocumentUploader $uploader): Response
    {
        $response = $uploader->handleDownload($document);

        return $response;
    }
}
