<?php

namespace App\Command;

use App\Entity\DigitalPost;
use App\Repository\DigitalPostRepository;
use App\Service\DigitalPostHelper;
use App\Service\DocumentUploader;
use App\Service\IdentificationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'tvist1:digital-post:send',
    description: 'Send unsent digital post',
)]
class DigitalPostSendCommand extends Command
{
    private int $maxNumberOfRetries = 10;

    public function __construct(private readonly DigitalPostHelper $digitalPostHelper, private readonly DigitalPostRepository $digitalPostRepository, private readonly DocumentUploader $documentUploader, private readonly EntityManagerInterface $entityManager, private readonly LoggerInterface $databaseLogger)
    {
        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->addOption('force-send', null, InputOption::VALUE_NONE, 'Force sending a digital post even if failed or already sent')
            ->addOption('digital-post-id', null, InputOption::VALUE_REQUIRED, 'Id of digital post to send')
            ->addOption('recipient-identifier', null, InputOption::VALUE_REQUIRED, 'Overwrite recipient identifier (CPR or CVR)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $statuses = [null, DigitalPost::STATUS_ERROR];

        $id = $input->getOption('digital-post-id');
        $forceSend = false;
        $overwriteRecipientIdentifier = null;
        if (null !== $id) {
            $digitalPost = $this->digitalPostRepository->find($id);
            if (null === $digitalPost) {
                throw new RuntimeException(sprintf('Invalid digital post id %s. Use tvist1:digital-post:list to show all digital posts.', $id));
            }
            $digitalPosts = [$digitalPost];
            $forceSend = $input->getOption('force-send');
        } else {
            $digitalPosts = $this->digitalPostRepository->findBy(['status' => $statuses]);
        }

        if ($forceSend) {
            $overwriteRecipientIdentifier = $input->getOption('recipient-identifier');
            if (null === $overwriteRecipientIdentifier) {
                throw new RuntimeException(sprintf('Options recipient-identifier must be specified when using force-send'));
            }
        }

        $io->info(sprintf('Number of digital posts: %d', count($digitalPosts)));

        foreach ($digitalPosts as $index => $digitalPost) {
            if (!$forceSend && !in_array($digitalPost->getStatus(), $statuses)) {
                $io->error(sprintf('Digital post %s has invalid status %s (expected one of %s)', $digitalPost->getId(), json_encode($digitalPost->getStatus(), JSON_THROW_ON_ERROR), implode(', ', array_map('json_encode', $statuses))));
                continue;
            }

            $io->title(sprintf('% 3d/%d %s:%s', $index + 1, count($digitalPosts), $digitalPost::class, $digitalPost->getId()));

            try {
                $content = $this->documentUploader->getFileContent($digitalPost->getDocument());
                $attachments = [];
                foreach ($digitalPost->getAttachments() as $attachment) {
                    $attachments[$attachment->getDocument()->getDocumentName()] = $this->documentUploader->getFileContent($attachment->getDocument());
                }

                $previousResults = $digitalPost->getData()['results'] ?? [];
                $results = [];
                foreach ($digitalPost->getRecipients() as $recipient) {
                    $recipientIdentifier = $overwriteRecipientIdentifier ?? $recipient->getIdentifier();

                    $recipientKey = $recipient->getId()->toRfc4122();
                    $io->info(sprintf('%s (%s: %s)', $recipient->getName(), $recipient->getIdentifierType(), $recipientIdentifier));

                    $previousResult = $previousResults[$recipientKey] ?? [];

                    $result = null;
                    if (!$forceSend && true === ($previousResult['result'] ?? null)) {
                        $io->info(sprintf('Already sent to %s', $recipient->getName()));
                        $result = $previousResult;
                    } else {
                        try {
                            switch ($recipient->getIdentifierType()) {
                            case IdentificationHelper::IDENTIFIER_TYPE_CPR:
                                $result = $this->digitalPostHelper->sendDigitalPostCPR(
                                    $recipientIdentifier,
                                    $recipient->getName(),
                                    $recipient->getAddress(),
                                    $digitalPost->getSubject(),
                                    $content,
                                    $attachments
                                );
                                break;

                             case IdentificationHelper::IDENTIFIER_TYPE_CVR:
                                 $result = $this->digitalPostHelper->sendDigitalPostCVR(
                                     $recipientIdentifier,
                                     $recipient->getName(),
                                     $recipient->getAddress(),
                                     $digitalPost->getSubject(),
                                     $content,
                                     $attachments
                                 );
                                 break;

                            default:
                                $result = [
                                    'result' => 'error',
                                    'message' => sprintf(
                                        'Unhandled identifier type: %s',
                                        $recipient->getIdentifierType()
                                    ),
                                ];
                                $io->error($result['message']);
                                break;
                            }
                        } catch (\Exception $exception) {
                            $this->databaseLogger->error($exception->getMessage());
                            $result = [
                                'result' => 'exception',
                                'message' => $exception->getMessage(),
                                'exception' => $exception,
                            ];
                            $io->error($result['message']);
                        }
                    }
                    $results[$recipientKey] = $result;
                }

                // Bookkeeping.
                $digitalPost->addData(['results' => $results]);

                $resultValues = array_unique(array_column($results, 'result'));

                $now = new \DateTimeImmutable();
                // The digital post has been sent to all recipients if all result values are true.
                $sent = 1 === count($resultValues) && true === $resultValues[0];
                $digitalPost->setStatus($sent ? DigitalPost::STATUS_SENT : DigitalPost::STATUS_ERROR);
                if (DigitalPost::STATUS_SENT === $digitalPost->getStatus()) {
                    $digitalPost->setSentAt($now);
                }

                // Keep track of posts and fail when max number of retries exceeded.
                $postStatuses = $digitalPost->getData()['post_statuses'] ?? [];
                if (!is_array($postStatuses)) {
                    $postStatuses = [];
                }
                $postStatuses[] = [
                    'created_at' => $now->format($now::ATOM),
                    'status' => $digitalPost->getStatus(),
                ];
                $digitalPost->addData(['post_statuses' => $postStatuses]);

                if (count($postStatuses) >= $this->maxNumberOfRetries) {
                    $digitalPost->setStatus(DigitalPost::STATUS_FAILED);
                }

                $this->entityManager->persist($digitalPost);
                $this->entityManager->flush();

                $io->info(sprintf('Status: %s', $digitalPost->getStatus()));
            } catch (\Exception $exception) {
                $digitalPost->setData([
                   'exception' => [
                       'message' => $exception->getMessage(),
                   ],
                ]);
                $this->databaseLogger->error($exception->getMessage());
                $io->error(sprintf('Error: %s', $exception->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
