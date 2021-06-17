<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\User;
use App\Exception\CaseNotFoundException;
use App\Exception\FileMovingException;
use App\Exception\TvistException;
use App\Form\DocumentType;
use App\Repository\CaseEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var CaseEntityRepository
     */
    private $caseRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(CaseEntityRepository $caseRepository, EntityManagerInterface $entityManager)
    {
        $this->caseRepository = $caseRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="document_index")
     * @throws TvistException
     */
    public function index(Request $request, string $case_id): Response
    {
        // The beneath can possibly be removed and done via 'guessing' the case instead
        $case = $this->caseRepository->find(['id' => $case_id]);

        if (null === $case) {
            throw new CaseNotFoundException('Case with id ' . $case_id . ' not found.');
        }

        $documents = $case->getDocuments();

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract file and handle it
            $file = $form->get('name')->getData();

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Beneath handles spaces, special chars and also danish specific letters
            $slugger = new AsciiSlugger();
            $safeFilename = $slugger->slug($originalFilename);

            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('file_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                throw new FileMovingException($e->getMessage());
            }

            $document->setName($newFilename);

            /** @var User $uploader */
            $uploader = $this->getUser();
            $document->setCreatedBy($uploader->getEmail());

            $document->addCase($case);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return $this->redirectToRoute('document_index', ['case_id' => $case_id]);
        }

        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
            'documents' => $documents,
            'document_form' => $form->createView(),
        ]);
    }
}
