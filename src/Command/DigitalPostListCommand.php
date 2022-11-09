<?php

namespace App\Command;

use _PHPStan_3e014c27f\Symfony\Component\Console\Exception\InvalidOptionException;
use App\Entity\CaseDocumentRelation;
use App\Entity\DigitalPost;
use App\Repository\DigitalPostRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'tvist1:digital-post:list',
    description: 'List digital post',
)]
class DigitalPostListCommand extends Command
{
    private const STATUS_NULL = 'null';

    public function __construct(private DigitalPostRepository $digitalPostRepository, private UrlGeneratorInterface $urlGenerator)
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
            $urls = array_map(
                fn (CaseDocumentRelation $relation) => $this->urlGenerator->generate('digital_post_show', ['id' => $relation->getCase()->getId(), 'digitalPost' => $digitalPost->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $digitalPost->getDocument()->getCaseDocumentRelations()->toArray()
            );

            $io->definitionList(
                ['Id' => $digitalPost->getId()],
                ['Subject' => $digitalPost->getSubject()],
                ['Status' => $digitalPost->getStatus() ?? self::STATUS_NULL],
                ['Recipients' => implode(PHP_EOL, $digitalPost->getRecipients()->map(static fn (DigitalPost\Recipient $recipient) => (string) $recipient)->toArray())],
                ['Created at' => $digitalPost->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $digitalPost->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
                ['Url' => implode(PHP_EOL, $urls)],
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
