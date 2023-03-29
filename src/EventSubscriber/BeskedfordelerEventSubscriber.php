<?php

namespace App\EventSubscriber;

use App\Entity\DigitalPostEnvelope;
use App\Repository\DigitalPostEnvelopeRepository;
use Itkdev\BeskedfordelerBundle\Event\PostStatusBeskedModtagEvent;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeskedfordelerEventSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    public function __construct(private MessageHelper $messageHelper, private DigitalPostEnvelopeRepository $envelopeRepository, LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    public static function getSubscribedEvents()
    {
        return [
            PostStatusBeskedModtagEvent::class => 'postStatusBeskedModtag',
        ];
    }

    public function postStatusBeskedModtag(PostStatusBeskedModtagEvent $event): void
    {
        try {
            $beskedfordelerMessage = $event->getDocument()->saveXML();
            $data = $this->messageHelper->getBeskeddata($beskedfordelerMessage);
            if ($messageUuid = ($data['MessageUUID'] ?? null)) {
                $envelope = $this->envelopeRepository->findOneBy(['meMoMessageUuid' => $messageUuid])
                    // TODO Find out how Beskedfordeleren actually sends messages on “Fjernprint“ messages.
                    ?? $this->envelopeRepository->findOneBy(['forsendelseUuid' => $messageUuid]);
                if (null !== $envelope) {
                    // We may receive the same message multiple times.
                    if (!in_array($beskedfordelerMessage, $envelope->getBeskedfordelerMessages(), true)) {
                        $envelope
                            ->addBeskedfordelerMessage($beskedfordelerMessage)
                            ->setStatusMessage($data['TransaktionsStatusKode'])
                        ;

                        if (!empty($data['FejlDetaljer'])) {
                            $envelope->setStatus(DigitalPostEnvelope::STATUS_FAILED);
                        } else {
                            // @todo physical post may generate multiple messages.
                            $envelope->setStatus(DigitalPostEnvelope::STATUS_DELIVERED);
                        }

                        $this->envelopeRepository->save($envelope, true);
                        $this->logger->info(sprintf('Beskedfordeler message "%s" added to message %s',
                            $data['TransaktionsStatusKode'] ?? null, $messageUuid));
                    }
                } else {
                    $this->logger->warning(sprintf('Unknown Beskedfordeler MeMo message uuid: %s', $messageUuid));
                }
            } else {
                $this->logger->warning(sprintf('Unhandled Beskedfordeler message; data: %s', json_encode($data)), [
                    'message' => $beskedfordelerMessage,
                ]);
            }
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Error handling Beskedfordeler message: %s', $exception->getMessage()), [
                'exception' => $exception,
                'event' => $event,
            ]);
        }
    }
}
