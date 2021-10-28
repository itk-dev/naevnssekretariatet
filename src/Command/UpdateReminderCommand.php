<?php

namespace App\Command;

use App\Service\ReminderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateReminderCommand extends Command
{
    protected static $defaultName = 'tvist1:update-reminder';
    protected static $defaultDescription = 'Updates reminder statuses to active if date reached';
    /**
     * @var ReminderHelper
     */
    private $reminderHelper;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ReminderHelper $reminderHelper)
    {
        $this->reminderHelper = $reminderHelper;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hasUpdated = $this->reminderHelper->updateStatuses();
        if (!$hasUpdated) {
            return Command::FAILURE;
        } else {
            return Command::SUCCESS;
        }
    }
}
