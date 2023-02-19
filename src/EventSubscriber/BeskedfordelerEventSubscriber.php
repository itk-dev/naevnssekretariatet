<?php

namespace App\EventSubscriber;

use Itkdev\BeskedfordelerBundle\Event\PostStatusBeskedModtagEvent;
use Itkdev\BeskedfordelerBundle\Helper\MessageHelper;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeskedfordelerEventSubscriber implements EventSubscriberInterface
{
    use LoggerAwareTrait;

    private MessageHelper $messageHelper;

    public function __construct(MessageHelper $messageHelper, LoggerInterface $logger)
    {
        $this->messageHelper = $messageHelper;
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
            $data = $this->messageHelper->getBeskeddata($event->getDocument()->saveXML());
            $this->logger->debug(json_encode(['data' => $data]));
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), [
                'exception' => $exception,
                'event' => $event,
            ]);
        }
    }
}
