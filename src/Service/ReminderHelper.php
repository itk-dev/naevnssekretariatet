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

        // Handle pending => active transition
        $currentDate = new DateTime('today');

        $pendingReminders = $this->reminderRepository->findBy([
            'status' => ReminderStatus::Pending,
        ]);

        foreach ($pendingReminders as $reminder) {
            $isToday = 0 === $currentDate->diff($reminder->getDate())->days;

            // Todo: Only do this for dates that have not yet been reached.
            if ($isToday) {
                $reminder->setStatus(ReminderStatus::Active);
                $this->entityManager->flush();
            }
        }

        // Handle active => exceeded transition
        $activeReminders = $this->reminderRepository->findBy([
            'status' => ReminderStatus::Active,
        ]);

        foreach ($activeReminders as $reminder) {
            $reminderDate = $reminder->getDate();
            $isExceeded = ($reminderDate < $currentDate) && (0 !== $currentDate->diff($reminderDate)->days);

            if ($isExceeded) {
                $reminder->setStatus(ReminderStatus::Exceeded);
                $this->entityManager->flush();
            }
        }

        return true;
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

    public function getStatusByDate(\DateTimeInterface $reminderDate): int
    {
        $today = new DateTime('today');

        if ($reminderDate > $today) {
            return ReminderStatus::Pending;
        } elseif ($reminderDate < $today) {
            return ReminderStatus::Exceeded;
        } else {
            return ReminderStatus::Active;
        }
    }
}
