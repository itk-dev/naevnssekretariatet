<?php

namespace App\Command;

use App\Exception\CprException;
use App\Exception\CvrException;
use App\Service\CvrHelper;
use GuzzleHttp\Exception\GuzzleException;
use ItkDev\Serviceplatformen\Certificate\Exception\CertificateLocatorException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            ->addArgument('cpr-number', InputArgument::REQUIRED, 'CVR number to look up')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cvr = $input->getArgument('cpr-number');

        try {
            $CVRData = $this->cvrHelper->lookupCvr((int) $cvr);

            $output->writeln([
                $cvr,
                json_encode($CVRData, JSON_PRETTY_PRINT),
            ]);
        } catch (CvrException $e) {
            $output->write($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
