<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Municipality;
use App\Entity\ResidentComplaintBoardCase;
use App\Repository\BoardRepository;
use App\Repository\MunicipalityRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CreateCaseController extends AbstractController
{
    /**
     * @var BoardRepository
     */
    private $boardRepository;

    /**
     * @var MunicipalityRepository
     */
    private $municipalityRepository;

    public function __construct(BoardRepository $boardRepository, MunicipalityRepository $municipalityRepository)
    {
        $this->boardRepository = $boardRepository;
        $this->municipalityRepository = $municipalityRepository;
    }

    /**
     * @Route("/municipality/{municipality_name}/board/{board_name}/case/create", name="rescase")
     */
    public function createCase(Request $request, string $municipality_name, string $board_name): Response
    {
        // Check that municipality exists
        $municipality = $this->findMunicipality($municipality_name);

        // Check that board exists
        $board = $this->findBoard($board_name);

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

                $slugger = new AsciiSlugger();

                foreach ($documents as $document) {
                    $originalFilename = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);

                    // todo: Make sure original file name is ok, possibly use slugger
                    $safeOriginalFilename = $slugger->slug($originalFilename);

                    $newFileName = $safeOriginalFilename.'-'.uniqid().'.'.$document->guessExtension();

                    // Move the file to the directory where they are stored
                    try {
                        $document->move(
                            $this->getParameter('file_directory'),
                            $newFileName
                        );
                    } catch (FileException $e) {
                        throw new FileException('Error during file upload');
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

    public function findBoard(string $name): Board
    {
        $board = $this->boardRepository->findOneBy(['name' => $name]);

        if (null === $board) {
            throw new Exception('Board not found.');
        }

        return $board;
    }

    public function findMunicipality(string $name): Municipality
    {
        $municipality = $this->municipalityRepository->findOneBy(['name' => $name]);

        if (null === $municipality) {
            throw new Exception('Municipality not found.');
        }

        return $municipality;
    }
}
