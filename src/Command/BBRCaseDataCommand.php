<?php

namespace App\Command;

use App\Exception\BBRException;
use App\Repository\CaseEntityRepository;
use App\Service\BBRHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BBRCaseDataCommand extends Command
{
    protected static $defaultName = 'tvist1:bbr:update-case-data';
    protected static $defaultDescription = 'Update BBR data on cases addresses';

    public function __construct(private readonly BBRHelper $bbrHelper, private readonly CaseEntityRepository $caseEntityRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('case-ids', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Optional case ids to fetch data for')
            ->addOption('address-property', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The address property to handle', ['leaseAddress'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $caseIds = $input->getArgument('case-ids');
        $addressProperties = $input->getOption('address-property');

        $cases = empty($caseIds)
            ? $this->caseEntityRepository->findAll()
            : $this->caseEntityRepository->findBy(['id' => $caseIds]);
        foreach ($cases as $case) {
            foreach ($addressProperties as $addressType) {
                try {
                    $io->info(sprintf('Case #%s: %s', $case->getId(), $case->getCaseNumber()));
                    $this->bbrHelper->updateCaseBBRData($case, $addressType);
                } catch (BBRException $exception) {
                    $io->error($exception->getMessage());
                }
            }
        }

        return Command::SUCCESS;
    }
}
