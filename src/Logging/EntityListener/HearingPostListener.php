<?php

namespace App\Logging\EntityListener;

use App\Entity\HearingPost;
use App\Entity\HearingPostRequest;
use App\Entity\HearingPostResponse;
use App\Logging\ItkDevLoggingException;
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
        $action = sprintf('%s created', $this->getHearingPostType($hearingPost));
        $this->logActivity($action, $args);
    }

    public function postUpdate(HearingPost $hearingPost, LifecycleEventArgs $args)
    {
        $action = sprintf('%s updated', $this->getHearingPostType($hearingPost));
        $this->logActivity($action, $args);
    }

    public function preRemove(HearingPost $hearingPost, LifecycleEventArgs $args)
    {
        $action = sprintf('%s deleted', $this->getHearingPostType($hearingPost));
        $this->logActivity($action, $args);
    }

    private function getHearingPostType(HearingPost $hearingPost)
    {
        return match (true) {
            $hearingPost instanceof HearingPostRequest => 'Hearing post request',
            $hearingPost instanceof HearingPostResponse => 'Hearing post response',
            default => throw new ItkDevLoggingException(sprintf('Unhandled hearing post type %s found.', get_class($hearingPost)))
        };
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var HearingPost $hearingPost */
        $hearingPost = $args->getObject();

        if (null === $hearingPost->getHearing()) {
            return;
        }

        $logEntry = $this->createLogEntry($action, $hearingPost->getHearing()->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
