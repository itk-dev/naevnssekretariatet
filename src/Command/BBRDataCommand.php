<?php

namespace App\Command;

use App\Repository\CaseEntityRepository;
use App\Service\BBRHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BBRDataCommand extends Command
{
    protected static $defaultName = 'tvist1:bbr:fetch-data';
    protected static $defaultDescription = 'Fetch BBR data';

    private BBRHelper $bbrHelper;
    private CaseEntityRepository $caseEntityRepository;

    public function __construct(BBRHelper $bbrHelper, CaseEntityRepository $caseEntityRepository)
    {
        parent::__construct();
        $this->bbrHelper = $bbrHelper;
        $this->caseEntityRepository = $caseEntityRepository;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('addresses', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'One or more addresses')
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Dump fetched data')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Don\'t update stored data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $addresses = $input->getArgument('addresses');
        $dump = $input->getOption('dump');
        $dryRun = $input->getOption('dry-run');

        foreach ($addresses as $address) {
            $bbrData = $this->bbrHelper->getBBRData($address);
            if (null !== $bbrData && $dump) {
                $output->writeln([
                    $bbrData->getAddress(),
                    json_encode($bbrData->getData(), JSON_PRETTY_PRINT),
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
