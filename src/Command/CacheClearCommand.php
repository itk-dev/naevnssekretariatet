<?php

namespace App\Command;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tvist1:cache:clear',
    description: 'Clear Symfony Cache Component cache',
)]
class CacheClearCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ([
            // Add all caches used in the project here.
            new FilesystemAdapter(),
        ] as $cache) {
            if ($cache->clear()) {
                $io->success(sprintf('%s cache cleared', $cache::class));
            } else {
                $io->error(sprintf('Error clearing %s cache', $cache::class));
            }
        }

        return Command::SUCCESS;
    }
}
