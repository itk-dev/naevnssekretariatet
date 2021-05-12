<?php

namespace App\Controller;

use App\Entity\ResidentComplaintBoardCase;
use App\Form\ResidentComplaintBoardCaseType;
use App\Repository\BoardRepository;
use App\Repository\MunicipalityRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $case->setMunicipality($municipality);
        $case->setBoard($board);

        $case->setCaseType($caseType);

        $form = $this->createForm('App\\Form\\'.$caseType, $case, ['board' => $board]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
