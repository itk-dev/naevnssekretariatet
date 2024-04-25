<?php

namespace App\Command;

use App\Kernel;
use App\Message\DigitalPostMessage;
use App\Repository\DigitalPostRepository;
use App\Service\SF1601\DigitalPoster;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

use function Safe\json_encode;

#[AsCommand(
    name: 'tvist1:digital-post-envelope:send',
    description: 'Send digital post envelope',
)]
class DigitalPostEnvelopeSendCommand extends Command
{
    public function __construct(
        readonly private DigitalPostRepository $digitalPostRepository,
        readonly private DigitalPoster $digitalPoster,
        readonly private MessageBusInterface $bus,
        readonly private Kernel $kernel
    ) {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->addOption('digital-post-id', null, InputOption::VALUE_REQUIRED, 'The digital post id')
            ->addOption('digital-post-subject', null, InputOption::VALUE_REQUIRED, 'The digital post subject')
            ->addOption('dispatch', null, InputOption::VALUE_NONE, 'If set, the message bus will be used for sending the digital post')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force run in all environments')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $this->kernel->getEnvironment();
        if (!in_array($environment, ['dev', 'test'], true)) {
            if (!$input->getOption('force')) {
                throw new RuntimeException(sprintf('This command should only be run in the dev and test environments. Use --force to run it in the %s environment.', $environment));
            }
        }

        $io = new SymfonyStyle($input, $output);

        if ($id = $input->getOption('digital-post-id')) {
            $id = Uuid::fromString($id);
        }

        $criteria = array_filter([
            'id' => $id,
            'subject' => $input->getOption('digital-post-subject'),
        ]);

        if (empty($criteria)) {
            throw new InvalidOptionException(sprintf('At least one of --digital-post-id and --digital-post-subject must be specified.'));
        }

        $digitalPost = $this->digitalPostRepository->findOneBy($criteria);
        if (null === $digitalPost) {
            throw new RuntimeException(sprintf('Cannot find digital post %s', json_encode($criteria)));
        }

        $io->section('Recipients');
        foreach ($digitalPost->getRecipients() as $recipient) {
            $io->writeln(sprintf('%s (#%s)', $recipient, $recipient->getId()));
        }

        $question = sprintf('Send digital post %s to %d recipient(s)', $digitalPost->getSubject(), $digitalPost->getRecipients()->count());
        if ($io->confirm($question, !$input->isInteractive())) {
            $dispatch = $input->getOption('dispatch');

            foreach ($digitalPost->getRecipients() as $recipient) {
                $message = new DigitalPostMessage($digitalPost, $recipient);
                if ($dispatch) {
                    $io->info(sprintf('Dispatching %s for sending to %s', $digitalPost, $recipient));
                    $this->bus->dispatch($message);
                } else {
                    $io->info(sprintf('Sending %s to %s', $digitalPost, $recipient));
                    $this->digitalPoster->sendDigitalPost($digitalPost, $recipient);
                }
            }
        }

        return self::SUCCESS;
    }
}
