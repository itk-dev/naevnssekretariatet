<?php

namespace App\MessageHandler;

use App\Entity\DigitalPost;
use App\Message\DigitalPostMessage;
use App\Repository\DigitalPostRepository;
use App\Service\SF1601\DigitalPoster;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Uid\Uuid;

use function Safe\json_encode;

#[AsMessageHandler]
class DigitalPostMessageHandler implements MessageHandlerInterface
{
    use LoggerAwareTrait;

    public function __construct(readonly private DigitalPostRepository $digitalPostRepository, readonly private DigitalPoster $digitalPoster, LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * @see https://symfony.com/doc/current/messenger.html#retries-failures
     */
    public function __invoke(DigitalPostMessage $message)
    {
        $this->logger->debug(sprintf('Handling %s: %s', $message::class, json_encode($message)));
        $digitalPost = $this->digitalPostRepository->find($message->getDigitalPostId());
        if (null === $digitalPost) {
            throw new UnrecoverableMessageHandlingException(sprintf('Cannot find digital post %s', $message->getDigitalPostId()));
        }
        $recipient = $this->findRecipient($digitalPost, $message->getRecipientId());
        if (null === $recipient) {
            throw new UnrecoverableMessageHandlingException(sprintf('Cannot find digital post recipient %s', $message->getRecipientId()));
        }

        $this->digitalPoster->sendDigitalPost($digitalPost, $recipient);
    }

    private function findRecipient(DigitalPost $digitalPost, Uuid $recipientId): ?DigitalPost\Recipient
    {
        foreach ($digitalPost->getRecipients() as $recipient) {
            if ($recipientId->equals($recipient->getId())) {
                return $recipient;
            }
        }

        return null;
    }
}
