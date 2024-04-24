<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReminderRepository;
use App\Service\ReminderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $activeReminders = $this->reminderRepository->findRemindersWithDifferentStatusByUser(ReminderStatus::PENDING, $user);

        return $this->render('navbar/_reminders.html.twig', [
            'active_reminders' => sizeof($activeReminders),
        ]);
    }

    public function renderShortcuts(): Response
    {
        if (!($this->isGranted('ROLE_CASEWORKER') || $this->isGranted('ROLE_ADMINISTRATION'))) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $rawShortcuts = explode(
            PHP_EOL,
            $user->getShortcuts()
        );

        $shortcuts = [];

        foreach ($rawShortcuts as $rawShortcut) {
            if (!str_contains($rawShortcut, ':')) {
                continue;
            }
            $data = explode(
                ':',
                $rawShortcut,
                2
            );
            $shortcuts[] = [
                'identifier' => trim($data[0]),
                'url' => trim($data[1]),
            ];
        }

        return $this->render('navbar/_shortcuts.html.twig', [
            'shortcuts' => $shortcuts,
        ]);
    }
}
