<?php

namespace App\Command;

use App\Exception\CprException;
use App\Service\CprHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CPRLookupCommand extends Command
{
    protected static $defaultName = 'tvist1:cpr:lookup';
    protected static $defaultDescription = 'Looks up CPR number';

    public function __construct(private readonly CprHelper $cprHelper)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('cpr-number', InputArgument::REQUIRED, 'CPR number to look up')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cpr = $input->getArgument('cpr-number');

        try {
            $cprData = $this->cprHelper->lookupCpr($cpr);

            $output->writeln([
                $cpr,
                json_encode($cprData, JSON_PRETTY_PRINT),
            ]);
        } catch (CprException $e) {
            $output->write($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
