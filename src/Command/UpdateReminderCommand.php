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
    protected static $defaultDescription = 'Updates reminder statuses';
    /**
     * @var ReminderHelper
     */
    private $reminderHelper;

    public function __construct(ReminderHelper $reminderHelper)
    {
        $this->reminderHelper = $reminderHelper;
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
        if ($hasUpdated) {
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }
    }
}
