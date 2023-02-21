<?php

namespace App\Command;

use App\Repository\CaseEntityRepository;
use App\Service\CaseManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tvist1:case:delete',
    description: 'Delete a case',
)]
class CaseDeleteCommand extends Command
{
    public function __construct(private readonly CaseEntityRepository $caseRepository, private readonly CaseManager $caseManager)
    {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('case-number', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The case number')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $caseNumbers = $input->getArgument('case-number');
        foreach ($caseNumbers as $caseNumber) {
            $case = $this->caseRepository->findOneBy(['caseNumber' => $caseNumber]);
            if (null === $case) {
                $io->error(sprintf('Cannot find case with number %s', $caseNumber));
                continue;
            }
            $this->caseManager->deleteCase($case);
            $io->success(sprintf('Case %s deleted', $caseNumber));
        }

        return Command::SUCCESS;
    }
}
