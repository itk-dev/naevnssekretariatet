<?php

namespace App\Command;

use App\Repository\CaseEntityRepository;
use App\Service\BBRHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BBRCaseDataCommand extends Command
{
    protected static $defaultName = 'tvist1:bbr:case-data';
    protected static $defaultDescription = 'Fetch BBR data on cases';

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
            ->addArgument('case-ids', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Optional case ids to fetch data for')
            ->addOption('address-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The address types to handle', ['lease'])
            ->addOption('dump', null, InputOption::VALUE_NONE, 'Dump fetched data')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Don\'t store (or update) data on cases')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $caseIds = $input->getArgument('case-ids');
        $addressTypes = $input->getOption('address-type');
        $dump = $input->getOption('dump');
        $dryRun = $input->getOption('dry-run');

        $cases = $this->caseEntityRepository->findAll();
        foreach ($cases as $case) {
            foreach ($addressTypes as $addressType) {
                $data = $this->bbrHelper->updateBBRData($case, $addressType, $dryRun);
            }
        }

        return Command::SUCCESS;
    }
}
