<?php

namespace App\Command;

use _PHPStan_3e014c27f\Symfony\Component\Console\Exception\InvalidOptionException;
use App\Entity\DigitalPost;
use App\Repository\DigitalPostRepository;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tvist1:digital-post:list',
    description: 'List digital post',
)]
class DigitalPostListCommand extends Command
{
    private const STATUS_NULL = 'null';

    public function __construct(private DigitalPostHelper $digitalPostHelper, private DigitalPostRepository $digitalPostRepository, private DocumentUploader $documentUploader, private EntityManagerInterface $entityManager, private LoggerInterface $databaseLogger)
    {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this->addOption('status', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Show post with this status. Allowed values: '.implode(', ', $this->getValidStatusNames()));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $statuses = $input->getOption('status') ?: $this->getValidStatuses();
        // Map null and empty status to real null value.
        $statuses = array_map(static fn ($status) => in_array($status, [self::STATUS_NULL, ''], true) ? null : $status, $statuses);
        $invalidStatuses = array_diff($statuses, $this->getValidStatuses());

        if (!empty($invalidStatuses)) {
            throw new InvalidOptionException(1 === count($invalidStatuses) ? sprintf('Invalid status: %s', reset($invalidStatuses)) : sprintf('Invalid statuses: %s', implode(', ', $invalidStatuses)));
        }

        $digitalPosts = $this->digitalPostRepository->findBy(['status' => $statuses]);

        $io->info(sprintf('Number of digital posts: %d', count($digitalPosts)));

        foreach ($digitalPosts as $digitalPost) {
            $io->definitionList(
                ['Id' => $digitalPost->getId()],
                ['Status' => $digitalPost->getStatus() ?? self::STATUS_NULL],
                ['Recipients' => implode(PHP_EOL, $digitalPost->getRecipients()->map(static fn (DigitalPost\Recipient $recipient) => (string) $recipient)->toArray())],
                ['Created at' => $digitalPost->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $digitalPost->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
            );
        }

        return Command::SUCCESS;
    }

    private function getValidStatuses(): array
    {
        return array_merge(DigitalPost::STATUSES, [null]);
    }

    private function getValidStatusNames(): array
    {
        return array_map(static fn ($status) => $status ?? self::STATUS_NULL, $this->getValidStatuses());
    }
}
