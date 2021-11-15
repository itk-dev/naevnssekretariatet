<?php

namespace App\Controller;

use App\Entity\CaseEntity;
use App\Entity\Reminder;
use App\Entity\User;
use App\Form\ReminderType;
use App\Service\ReminderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/case/{id}/reminder")
 */
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
     * @Route("/new", name="reminder_create", methods={"POST"})
     */
    public function new(CaseEntity $case, Request $request): Response
    {
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

            return $this->redirectToRoute('case_index');
        }

        return $this->render('reminder/_new.html.twig', [
            'reminder_form' => $reminderForm->createView(),
            'case' => $case,
        ]);
    }

    /**
     * @Route("/{reminder_id}/complete", name="reminder_complete", methods={"POST"})
     * @Entity("reminder", expr="repository.find(reminder_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function complete(CaseEntity $case, Reminder $reminder): Response
    {
        $this->entityManager->remove($reminder);
        $this->entityManager->flush();

        return $this->redirectToRoute('reminder_index');
    }

    /**
     * @Route("/{reminder_id}/edit", name="reminder_edit", methods={"POST"})
     * @Entity("reminder", expr="repository.find(reminder_id)")
     * @Entity("case", expr="repository.find(id)")
     */
    public function edit(CaseEntity $case, Reminder $reminder, Request $request): Response
    {
        $reminderForm = $this->createForm(ReminderType::class, $reminder);

        $reminderForm->handleRequest($request);

        if ($reminderForm->isSubmitted() && $reminderForm->isValid()) {
            /** @var Reminder $reminder */
            $reminder = $reminderForm->getData();
            // TODO: What if it is still same date and just content edit
            $reminder->setStatus($this->reminderHelper->getStatusByDate($reminder->getDate()));

            $this->entityManager->flush();

            return $this->redirectToRoute('reminder_index');
        }

        return $this->render('reminder/_edit.html.twig', [
            'reminder_form' => $reminderForm->createView(),
            'reminder' => $reminder,
        ]);
    }
}
