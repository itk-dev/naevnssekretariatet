<?php

namespace App\Command;

use App\Entity\CaseDocumentRelation;
use App\Entity\DigitalPost;
use App\Entity\DigitalPostAttachment;
use App\Entity\DigitalPostEnvelope;
use App\Entity\Document;
use App\Repository\DigitalPostRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $digitalPosts = $this->digitalPostRepository->findAll();

        $io->info(sprintf('Number of digital posts: %d', count($digitalPosts)));

        foreach ($digitalPosts as $digitalPost) {
            $urls = array_map(
                fn (CaseDocumentRelation $relation) => $this->urlGenerator->generate('digital_post_show', ['id' => $relation->getCase()->getId(), 'digitalPost' => $digitalPost->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                $digitalPost->getDocument()->getCaseDocumentRelations()->toArray()
            );

            $io->definitionList(
                ['Id' => $digitalPost->getId()],
                ['Subject' => $digitalPost->getSubject()],
                ['Subject (truncated)' => $digitalPost->getSubject(true)],
                ['Statuses' => implode(', ', $digitalPost->getStatuses() ?? [])],
                ['Recipients' => implode(PHP_EOL, $digitalPost->getRecipients()->map(static fn (DigitalPost\Recipient $recipient) => (string) $recipient)->toArray())],
                ['Created at' => $digitalPost->getCreatedAt()->format(\DateTimeInterface::ATOM)],
                ['Updated at' => $digitalPost->getUpdatedAt()->format(\DateTimeInterface::ATOM)],
                ['Url' => implode(PHP_EOL, $urls)],
                ['Envelopes' => implode(PHP_EOL, array_map(static fn (DigitalPostEnvelope $envelope) => sprintf('%s: %s (%s)', $envelope->getStatus(), $envelope->getStatusMessage(), $envelope->getMeMoMessageUuid()),
                                                           iterator_to_array($digitalPost->getEnvelopes())))],
            );

            if ($output->isDebug()) {
                $documentToArray = static fn (Document $document) => [
                    'filename' => $document->getFilename(),
                    'uploaded_at' => $document->getUploadedAt(),
                ];
                $io->section('Debug');

                // Document and attachments.
                $document = $digitalPost->getDocument();
                $attachments = $digitalPost->getAttachments();
                $io->writeln(Yaml::dump(array_filter([
                    'document' => $documentToArray($document),
                    'attachments' => array_map(static fn (DigitalPostAttachment $attachment) => [
                        'document' => $documentToArray($attachment->getDocument()),
                    ], $attachments->toArray()),
                ]), PHP_INT_MAX));

                $io->writeln(Yaml::dump(array_filter([
                    'data' => $digitalPost->getData(),
                ]), PHP_INT_MAX));
            }
        }

        return Command::SUCCESS;
    }
}
