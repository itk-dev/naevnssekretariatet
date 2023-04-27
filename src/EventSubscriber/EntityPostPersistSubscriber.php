<?php

namespace App\EventSubscriber;

use App\Entity\HearingPostResponse;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class EntityPostPersistSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof HearingPostResponse) {
            $case = $entity->getHearing()->getCaseEntity();
            $case->setHearingResponseDeadline(null);
            $case->setHasReachedHearingResponseDeadline(false);
        }
    }
}
