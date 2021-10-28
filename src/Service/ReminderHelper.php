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

    public function getUpcomingRemindersByUser(User $user): array
    {
        $reminders = $this->reminderRepository->findBy([
            'createdBy' => $user->getId()->toBinary(),
        ], [
            'date' => 'ASC',
        ]);

        $today = new DateTime('today');

        $upcomingReminders = [];

        foreach ($reminders as $reminder) {
            $reminderDate = $reminder->getDate();

            // Check if is within 7 days of today
            if ($reminderDate >= $today) {
                $daysDiff = $today->diff($reminderDate)->days;

                // TODO: Allow user to select 7, 14 or 30 days?
                if (7 <= $daysDiff) {
                    // Break out remaining reminders since we got them ordered with ascending date
                    break;
                }

                // Check if we have already added a reminder from this day to upcomingReminders
                if (array_key_exists($daysDiff, $upcomingReminders)) {
                    $currentReminders = $upcomingReminders[$daysDiff]['reminders'];
                    array_push($currentReminders, $reminder);
                    $upcomingReminders[$daysDiff]['reminders'] = $currentReminders;
                } else {
                    // TODO: If above todo, then this should be revisited
                    if (0 === $daysDiff) {
                        $weekday = 'Today';
                    } elseif (1 === $daysDiff) {
                        $weekday = 'Tomorrow';
                    } else {
                        $weekday = $reminderDate->format('l');
                    }

                    $upcomingReminders[$daysDiff] = [
                        'weekday' => $weekday,
                        'reminders' => [
                            $reminder,
                        ],
                    ];
                }
            }
        }

        return $upcomingReminders;
    }
}
