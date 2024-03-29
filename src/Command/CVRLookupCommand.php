<?php

namespace App\Command;

use App\Exception\CvrException;
use App\Service\CvrHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CVRLookupCommand extends Command
{
    protected static $defaultName = 'tvist1:cvr:lookup';
    protected static $defaultDescription = 'Looks up CVR number';

    public function __construct(private CvrHelper $cvrHelper)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('cvr-number', InputArgument::REQUIRED, 'CVR number to look up')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cvr = $input->getArgument('cvr-number');

        try {
            $cvrData = $this->cvrHelper->lookupCvr($cvr);

            $output->writeln([
                $cvr,
                json_encode($cvrData, JSON_PRETTY_PRINT),
            ]);
        } catch (CvrException $e) {
            $output->write($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
