<?php

namespace App\Logging\EntityListener;

use App\Entity\Hearing;
use App\Entity\Party;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class HearingListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    public function postPersist(Hearing $hearing, LifecycleEventArgs $args)
    {
        $this->logActivity('Hearing created', $args);
    }

    public function postUpdate(Hearing $hearing, LifecycleEventArgs $args)
    {
        $this->logActivity('Hearing updated', $args);
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var Hearing $hearing */
        $hearing = $args->getObject();

        $logEntry = $this->createLogEntry($action, $hearing->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
