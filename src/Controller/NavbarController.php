<?php

namespace App\Controller;

use App\Entity\Reminder;
use App\Entity\User;
use App\Service\ReminderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class NavbarController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    public function renderReminders(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $activeReminders = $user->getReminders()->filter(function (Reminder $reminder) {
            return ReminderStatus::Active === $reminder->getStatus();
        });

        return $this->render('navbar/_reminders.html.twig', [
            'active_reminders' => sizeof($activeReminders),
        ]);
    }
}
