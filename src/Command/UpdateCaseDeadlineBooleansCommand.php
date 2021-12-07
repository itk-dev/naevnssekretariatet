<?php

namespace App\Command;

use App\Service\CaseManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCaseDeadlineBooleansCommand extends Command
{
    protected static $defaultName = 'tvist1:update-case-deadlines';
    protected static $defaultDescription = 'Updates case deadline booleans';
    /**
     * @var CaseManager
     */
    private $caseManager;

    public function __construct(CaseManager $caseManager)
    {
        $this->caseManager = $caseManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Runs the case deadline update logic but prints changes rather than applying them.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->setVerbosity(128);
        $logger = new ConsoleLogger($output);

        $this->caseManager->setLogger($logger);

        $isDryRun = $input->getOption('dry-run');

        $this->caseManager->updateDeadlineBooleans($isDryRun);

        return Command::SUCCESS;
    }
}
