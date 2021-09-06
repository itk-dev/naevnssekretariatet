<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Note;
use App\Form\NoteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/case")
 */
class NoteController extends AbstractController
{
    /**
     * @Route("/{id}/notes", name="case_notes", methods={"GET", "POST"})
     */
    public function index(CaseEntity $case, EntityManagerInterface $entityManager, Security $security, Request $request): Response
    {
        $notes = $case->getNotes();

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $note->setCaseEntity($case);

            $note->setCreatedBy($security->getUser());
            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('case_notes', ['id' => $case->getId()]);
        }

        return $this->render('case/notes.html.twig', [
            'note_form' => $form->createView(),
            'case' => $case,
            'notes' => $notes,
        ]);
    }
}
