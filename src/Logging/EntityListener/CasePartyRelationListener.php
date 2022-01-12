<?php

namespace App\Logging\EntityListener;

use App\Entity\CasePartyRelation;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class CasePartyRelationListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    public function postUpdate(CasePartyRelation $relation, LifecycleEventArgs $args)
    {
        $this->logActivity('Party relation updated', $args);
    }

    public function postPersist(CasePartyRelation $relation, LifecycleEventArgs $args)
    {
        $this->logActivity('Party relation added', $args);
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var CasePartyRelation $relation */
        $relation = $args->getObject();

        $logEntry = $this->createLogEntry($action, $relation->getCase(), $args);

        $em->persist($logEntry);
        $em->flush();
    }
}
