<?php

namespace App\Logging\EntityListener;

use App\Entity\HearingPost;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class HearingPostListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    public function postPersist(HearingPost $hearingPost, LifecycleEventArgs $args)
    {
        $this->logActivity('Hearing post created', $args);
    }

    public function postUpdate(HearingPost $hearingPost, LifecycleEventArgs $args)
    {
        $this->logActivity('Hearing post updated', $args);
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var HearingPost $hearingPost */
        $hearingPost = $args->getObject();

        $logEntry = $this->createLogEntry($action, $hearingPost->getHearing()->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
