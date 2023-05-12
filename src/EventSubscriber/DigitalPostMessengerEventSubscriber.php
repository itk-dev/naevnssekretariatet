<?php

namespace App\EventSubscriber;

use App\Entity\DigitalPostEnvelope;
use App\Message\DigitalPostMessage;
use App\Repository\DigitalPostEnvelopeRepository;
use App\Repository\DigitalPostRepository;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class DigitalPostMessengerEventSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    public function __construct(
        readonly private DigitalPostRepository $digitalPostRepository,
        readonly private DigitalPostEnvelopeRepository $envelopeRepository,
        LoggerInterface $logger
    ) {
        $this->setLogger($logger);
    }

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => 'workerMessageFailedEvent',
        ];
    }

    public function workerMessageFailedEvent(WorkerMessageFailedEvent $event): void
    {
        if (!$event->willRetry()) {
            $message = $event->getEnvelope()?->getMessage();
            if ($message instanceof DigitalPostMessage) {
                $digitalPost = $this->digitalPostRepository->find($message->getDigitalPostId());
                $envelope = $this->envelopeRepository->findOneBy(['digitalPost' => $digitalPost]);
                $envelope
                    ->setStatus(DigitalPostEnvelope::STATUS_FAILED_TOO_MANY_RETRIES)
                ;
                $this->envelopeRepository->save($envelope, true);
            }
        }
    }
}
