<?php

namespace App\Command;

use App\Service\SF1601\DigitalPoster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Safe\json_encode;

#[AsCommand(
    name: 'tvist1:digital-post:forespoerg',
    description: 'Forespørg',
)]
class DigitalPostForespoergCommand extends Command
{
    public function __construct(
        readonly private DigitalPoster $digitalPoster,
    ) {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->addArgument('identifier', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The identifier')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'The type (digitalpost, …)', 'digitalpost')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = $io->createTable();
        $table->setHeaders(['Identifier', 'Result']);
        $identifiers = array_unique($input->getArgument('identifier'));
        $type = $input->getOption('type');
        foreach ($identifiers as $identifier) {
            $result = $this->digitalPoster->canReceive($type, $identifier);
            $table->appendRow([$identifier, is_bool($result) ? json_encode($result) : $result]);
        }

        return self::SUCCESS;
    }
}
