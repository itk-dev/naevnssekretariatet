<?php

namespace App\EventSubscriber;

use App\Entity\Party;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['setIsPartOfPartIndex'],
        ];
    }

    /**
     * Ensures parties created in EasyAdmin section is added to part index.
     */
    public function setIsPartOfPartIndex(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Party)) {
            return;
        }

        $entity->setIsPartOfPartIndex(true);
    }
}
