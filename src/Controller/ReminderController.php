<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ReminderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ReminderController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/reminder", name="reminder_index")
     */
    public function index(ReminderRepository $reminderRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $reminders = $reminderRepository->findBy([
            'createdBy' => $user->getId()->toBinary(),
        ]);

        return $this->render('reminder/index.html.twig', [
            'reminders' => $reminders,
        ]);
    }
}
