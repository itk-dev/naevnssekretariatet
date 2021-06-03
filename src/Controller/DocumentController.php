<?php

namespace App\Controller;

use App\Entity\Document;
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
     * @Route("/", name="document_index")
     */
    public function index(CaseEntityRepository $caseEntityRepository, EntityManagerInterface $entityManager, Request $request, string $case_id): Response
    {
        // The beneath can possibly be removed and done via 'guessing' the case instead
        $case = $caseEntityRepository->find(['id' => $case_id]);

        if (null === $case) {
            throw new Exception('Case not found');
        }

        $documents = $case->getDocuments();

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Extract file and handle it
            $file = $form->get('name')->getData();

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // todo: modify orignal file name more than just ascii slug as beneath
            $slugger = new AsciiSlugger();
            $safeFilename = $slugger->slug($originalFilename);

            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('file_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                throw new FileException('Error moving file to directory.');
            }

            $document->setName($newFilename);

            // todo: get name of uploader and set
            $uploader = 'Test Testersen';
            $document->setCreatedBy($uploader);

            $document->addCase($case);

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('document_index', ['case_id' => $case_id]);
        }

        return $this->render('document/index.html.twig', [
            'controller_name' => 'DocumentController',
            'documents' => $documents,
            'document_form' => $form->createView(),
        ]);
    }
}
