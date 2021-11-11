<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReminderRepository;
use App\Service\ReminderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class NavbarController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var ReminderRepository
     */
    private $reminderRepository;

    public function __construct(Security $security, ReminderRepository $reminderRepository)
    {
        $this->security = $security;
        $this->reminderRepository = $reminderRepository;
    }

    public function renderReminders(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $activeReminders = $this->reminderRepository->findRemindersWithDifferentStatusByUser(ReminderStatus::Pending, $user);

        return $this->render('navbar/_reminders.html.twig', [
            'active_reminders' => sizeof($activeReminders),
        ]);
    }
}
