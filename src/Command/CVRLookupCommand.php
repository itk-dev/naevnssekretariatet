<?php

namespace App\Command;

use App\Exception\CprException;
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
            $this->cvrHelper->testCvrDatafordeler((int) $cvr);
        } catch (GuzzleException $e) {
            throw new CprException('test');
        } catch (CertificateLocatorException $e) {
            throw new CprException('test2');
        }


        return Command::SUCCESS;
    }
}
