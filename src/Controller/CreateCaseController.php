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
    public function rescase(BoardRepository $boardRepository, MunicipalityRepository $municipalityRepository, Request $request, string $municipality_name, string $board_name): Response
    {
        $rescase = new ResidentComplaintBoardCase();

        $municipality = $municipalityRepository->findOneBy(['name' => $municipality_name]);

        if (null === $municipality) {
            throw new Exception('Municipality not found.');
        }

        $board = $boardRepository->findOneBy(['name' => $board_name]);

        if (null === $board) {
            throw new Exception('Board not found.');
        }

        $rescase->setMunicipality($municipality);
        $rescase->setBoard($board);
        $rescase->setCaseType($board->getCaseFormType());

        $form = $this->createForm(ResidentComplaintBoardCaseType::class, $rescase);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rescase = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($rescase);
            $em->flush();

            return $this->redirectToRoute('default');
        }

        return $this->render('case/createRescase.html.twig', [
            'resident_complaint_form' => $form->createView(),
        ]);
    }
}
