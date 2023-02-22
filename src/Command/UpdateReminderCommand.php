<?php

namespace App\Command;

use App\Service\ReminderHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateReminderCommand extends Command
{
    protected static $defaultName = 'tvist1:update-reminder';
    protected static $defaultDescription = 'Updates reminder statuses';

    public function __construct(private readonly ReminderHelper $reminderHelper)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Runs the reminder status update logic but prints changes rather than applying them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->setVerbosity(128);
        $logger = new ConsoleLogger($output);

        $this->reminderHelper->setLogger($logger);

        $isDryRun = $input->getOption('dry-run');

        $this->reminderHelper->updateStatuses($isDryRun);

        return Command::SUCCESS;
    }
}
