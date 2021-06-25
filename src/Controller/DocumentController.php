<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Entity\User;
use App\Exception\FileMovingException;
use App\Exception\TvistException;
use App\Form\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @Route("/case/{case_id}/documents")
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
     * @Route("/", name="document_index")
     * @Entity("case", expr="repository.find(case_id)")
     *
     * @throws TvistException
     */
    public function index(Request $request, CaseEntity $case): Response
    {
        // Create new document and its form
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract filename and handle it
            // Users will only see document name, not filename
            $file = $form->get('filename')->getData();

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // AsciiSlugger handles spaces, special chars and danish specific letters
            $slugger = new AsciiSlugger();
            $safeFilename = $slugger->slug($originalFilename);

            // Make a safe and unique filename
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('file_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                throw new FileMovingException($e->getMessage());
            }

            // Set filename, document name, creator and case
            $document->setFilename($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setCreatedBy($uploader->getEmail());

            $document->addCase($case);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return $this->redirectToRoute('document_index', ['case_id' => $case->getId()]);
        }

        // Load documents to be shown
        $documents = $case->getDocuments();

        return $this->render('document/index.html.twig', [
            'documents' => $documents,
            'document_form' => $form->createView(),
        ]);
    }
}
