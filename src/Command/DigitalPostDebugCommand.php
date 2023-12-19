<?php

namespace App\Command;

use App\Service\SF1601\DigitalPoster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tvist1:digital-post:debug',
    description: 'Add a short description for your command',
)]
class DigitalPostDebugCommand extends Command
{
    public function __construct(
        readonly private DigitalPoster $digitalPoster
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'The action');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');

        switch ($action) {
            case 'getSAMLToken':
            case 'fetchSAMLToken':
                $sf1601 = $this->invoke($this->digitalPoster, 'getSF1601');
                $entityId = 'http://entityid.kombit.dk/service/kombipostafsend/1';
                $samlToken = $this->invoke($sf1601, $action, [$entityId]);

                $output->write(json_encode($samlToken));

                return Command::SUCCESS;
        }
    }

    /**
     * Invoke method on an object.
     */
    private function invoke(object $object, string $method, array $args = []): mixed
    {
        $method = new \ReflectionMethod($object, $method);
        $method->setAccessible(true);

        return $method->invoke($object, ...$args);
    }
}
