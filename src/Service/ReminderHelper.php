<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ReminderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ReminderHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ReminderRepository
     */
    private $reminderRepository;

    public function __construct(EntityManagerInterface $entityManager, ReminderRepository $reminderRepository)
    {
        $this->entityManager = $entityManager;
        $this->reminderRepository = $reminderRepository;
    }

    public function updateStatuses(): bool
    {
        // Todo: Consider leap days and daylight saving
        $currentDate = new DateTime('today');

        $reminders = $this->reminderRepository->findBy([
            'status' => ReminderStatus::Pending,
        ]);

        foreach ($reminders as $reminder) {
            $isToday = 0 === $currentDate->diff($reminder->getDate())->days;

            // Todo: Only do this for dates that have not yet been reached.
            if ($isToday) {
                $reminder->setStatus(ReminderStatus::Active);
                $this->entityManager->flush();
            }
        }

        return false;
    }

    public function getRemindersWithinWeekByUserGroupedByDay(User $user): array
    {
        $reminders = $this->reminderRepository->findRemindersWithinWeekByUser($user);

        $remindersGroupedByDate = [];

        foreach ($reminders as $reminder) {
            $key = $reminder->getDate()->format('d-m-Y');

            $remindersGroupedByDate[$key][] = $reminder;
        }

        return $remindersGroupedByDate;
    }
}
