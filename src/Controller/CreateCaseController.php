<?php

namespace App\Controller;

use App\Entity\ResidentComplaintBoardCase;
use App\Repository\BoardRepository;
use App\Repository\MunicipalityRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateCaseController extends AbstractController
{
    /**
     * @Route("/municipality/{municipality_name}/board/{board_name}/case/create", name="rescase")
     */
    public function createCase(BoardRepository $boardRepository, MunicipalityRepository $municipalityRepository, Request $request, string $municipality_name, string $board_name): Response
    {
        // Check that municipality exists
        $municipality = $municipalityRepository->findOneBy(['name' => $municipality_name]);

        if (null === $municipality) {
            throw new Exception('Municipality not found.');
        }

        // Check that board exists
        $board = $boardRepository->findOneBy(['name' => $board_name]);

        if (null === $board) {
            throw new Exception('Board not found.');
        }

        // Match on which case object to create
        $caseType = $board->getCaseFormType();

        $case = null;

        switch ($caseType) {
            case 'ResidentComplaintBoardCaseType':
                $case = new ResidentComplaintBoardCase();
                break;
        }

        if (null === $case) {
            throw new Exception('Case object was not created.');
        }

        $case->setMunicipality($municipality);
        $case->setBoard($board);

        $case->setCaseType($caseType);

        $form = $this->createForm('App\\Form\\'.$caseType, $case, ['board' => $board]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle document upload
            $documents = $form->get('documents')->getData();

            if ($documents) {
                $revisedDocumentPaths = [];
                foreach ($documents as $document) {
                    $originalFilename = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);

                    // todo: Make sure original file name is ok, possibly use slugger

                    $newFileName = $originalFilename.'.'.$document->guessExtension();

                    // Move the file to the directory where they are stored
                    try {
                        $document->move(
                            $this->getParameter('file_directory'),
                            $newFileName
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                        throw new FileException('Error moving file');
                    }
                    array_push($revisedDocumentPaths, $newFileName);
                }
                $case->setDocuments($revisedDocumentPaths);
            }
            $case = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($case);
            $em->flush();

            return $this->redirectToRoute('default');
        }

        return $this->render('case/createCase.html.twig', [
            'case_form' => $form->createView(),
        ]);
    }
}
