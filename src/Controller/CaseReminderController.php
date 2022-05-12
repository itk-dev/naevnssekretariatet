<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Reminder;
use App\Entity\User;
use App\Form\ReminderType;
use App\Repository\ReminderRepository;
use App\Service\ReminderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatableMessage;

class CaseReminderController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ReminderHelper
     */
    private $reminderHelper;
    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $entityManager, ReminderHelper $reminderHelper, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->reminderHelper = $reminderHelper;
        $this->security = $security;
    }

    /**
     * @Route("/reminder", name="reminder_index")
     */
    public function index(ReminderRepository $reminderRepository): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $reminders = $reminderRepository->findBy(['createdBy' => $user->getId()->toBinary()], ['date' => 'ASC']);

        return $this->render('reminder/index.html.twig', [
            'reminders' => $reminders,
        ]);
    }

    /**
     * @Route("/case/{id}/reminder/new", name="reminder_new", methods={"POST"})
     */
    public function new(CaseEntity $case, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $case);

        $reminder = new Reminder();

        $reminderForm = $this->createForm(ReminderType::class, $reminder);

        $reminderForm->handleRequest($request);

        if ($reminderForm->isSubmitted() && $reminderForm->isValid()) {
            /** @var Reminder $reminder */
            $reminder = $reminderForm->getData();
            $reminder->setCaseEntity($case);
            $reminder->setStatus($this->reminderHelper->getStatusByDate($reminder->getDate()));

            /** @var User $user */
            $user = $this->security->getUser();
            $reminder->setCreatedBy($user);

            $this->entityManager->persist($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Reminder created', [], 'case'));

            $redirectUrl = $request->headers->get('referer') ?? $this->generateUrl('case_index');

            return $this->redirect($redirectUrl);
        }

        return $this->render('reminder/_new.html.twig', [
            'reminder_form' => $reminderForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/reminder/{id}/complete", name="reminder_complete", methods={"DELETE"})
     */
    public function complete(Reminder $reminder, Request $request): Response
    {
        $this->denyAccessUnlessGranted('delete', $reminder);

        // Check that CSRF token is valid
        if ($this->isCsrfTokenValid('complete'.$reminder->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reminder);
            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Reminder completed', [], 'case'));
        }

        return $this->redirectToRoute('reminder_index');
    }

    /**
     * @Route("/reminder/{id}/edit", name="reminder_edit", methods={"POST"})
     */
    public function edit(Reminder $reminder, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit', $reminder);

        $reminderForm = $this->createForm(ReminderType::class, $reminder);

        $reminderForm->handleRequest($request);

        if ($reminderForm->isSubmitted() && $reminderForm->isValid()) {
            /** @var Reminder $reminder */
            $reminder = $reminderForm->getData();
            // TODO: What if it is still same date and just content edit
            $reminder->setStatus($this->reminderHelper->getStatusByDate($reminder->getDate()));

            $this->entityManager->flush();
            $this->addFlash('success', new TranslatableMessage('Reminder updated', [], 'case'));

            return $this->redirectToRoute('reminder_index');
        }

        return $this->render('reminder/_edit.html.twig', [
            'reminder_form' => $reminderForm->createView(),
            'reminder' => $reminder,
        ]);
    }
}
