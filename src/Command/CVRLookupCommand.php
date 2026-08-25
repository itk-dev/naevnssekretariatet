<?php

namespace App\Command;

use App\Exception\CvrException;
use App\Service\CvrHelper;
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
            ->addArgument('cvr-number', InputArgument::REQUIRED|InputArgument::IS_ARRAY, 'CVR number(s) to look up')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $jsonEncode = static fn (mixed $value) => json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

        $cvrs = $input->getArgument('cvr-number');
        foreach ($cvrs as $cvr) {
            try {
                $cvrData = $this->cvrHelper->lookupCvr($cvr);

                $io->definitionList(
                    ['CVR' => $cvr],
                    ['Raw data' => $jsonEncode($cvrData)],
                    ['Relevant data' => $jsonEncode($this->cvrHelper->collectRelevantData($cvrData))],
                );
            } catch (CvrException $e) {
                $io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
