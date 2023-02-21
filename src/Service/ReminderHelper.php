<?php

namespace App\Service;

use App\Entity\Municipality;
use App\Entity\User;
use App\Repository\ReminderRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ReminderHelper implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ReminderRepository $reminderRepository)
    {
    }

    /**
     * Inspects reminders with status pending and active checking
     * if their status should be updated to active and exceeded respectively.
     */
    public function updateStatuses(bool $dryRun)
    {
        // Handle pending => active transition
        $currentDate = new DateTime('today');

        $this->logger->info('Today: '.$currentDate->format('d/m/Y'));

        // Handle pending => active status transition
        $pendingReminders = $this->reminderRepository->findBy([
            'status' => ReminderStatus::PENDING,
        ]);

        foreach ($pendingReminders as $reminder) {
            $isToday = 0 === $currentDate->diff($reminder->getDate())->days;

            if ($isToday) {
                $this->logger->info('Changing reminder with date: '.$reminder->getDate()->format('d/m/Y').' from status PENDING to ACTIVE');

                if (!$dryRun) {
                    $reminder->setStatus(ReminderStatus::ACTIVE);
                    $this->entityManager->flush();
                }
            }
        }

        // Handle active => exceeded status transition
        $activeReminders = $this->reminderRepository->findBy([
            'status' => ReminderStatus::ACTIVE,
        ]);

        foreach ($activeReminders as $reminder) {
            $reminderDate = $reminder->getDate();
            $isExceeded = ($reminderDate < $currentDate) && (0 !== $currentDate->diff($reminderDate)->days);

            if ($isExceeded) {
                $this->logger->info('Changing reminder with date: '.$reminder->getDate()->format('d/m/Y').' from status ACTIVE to EXCEEDED');

                if (!$dryRun) {
                    $reminder->setStatus(ReminderStatus::EXCEEDED);
                    $this->entityManager->flush();
                }
            }
        }
    }

    public function getRemindersWithinWeekByUserAndMunicipalityGroupedByDay(User $user, Municipality $municipality): array
    {
        $reminders = $this->reminderRepository->findRemindersWithinWeekByUserAndMunicipality($user, $municipality);

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
            return ReminderStatus::PENDING;
        } elseif ($reminderDate < $today) {
            return ReminderStatus::EXCEEDED;
        } else {
            return ReminderStatus::ACTIVE;
        }
    }
}
