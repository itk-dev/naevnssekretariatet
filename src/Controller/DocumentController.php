<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\User;
use App\Form\DocumentType;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="case_documents", methods={"GET"})
     */
    public function index(CaseEntity $case): Response
    {
        $documents = $case->getDocuments();

        return $this->render('documents/index.html.twig', [
            'case' => $case,
            'documents' => $documents,
        ]);
    }

    /**
     * @Route("/create", name="case_documents_create", methods={"GET", "POST"})
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

            $document->addCase($case);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return $this->redirectToRoute('case_documents', ['id' => $case->getId()]);
        }

        return $this->render('documents/create.html.twig', [
            'case' => $case,
            'document_form' => $form->createView(),
        ]);
    }
}
