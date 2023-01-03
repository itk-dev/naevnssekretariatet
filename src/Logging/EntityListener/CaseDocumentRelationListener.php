<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseDocumentRelation;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CaseDocumentRelationListener extends AbstractEntityListener
{
    public function postPersist(CaseDocumentRelation $relation, LifecycleEventArgs $args)
    {
        $this->logActivity('Document added', $args);
    }

    public function postUpdate(CaseDocumentRelation $relation, LifecycleEventArgs $args)
    {
        $this->logActivity('Document updated', $args);
    }

    public function preRemove(CaseDocumentRelation $relation, LifecycleEventArgs $args)
    {
        $this->logActivity('Document deleted', $args);
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var CaseDocumentRelation $relation */
        $relation = $args->getObject();

        $logEntry = $this->createLogEntry($action, $relation->getCase(), $args);

        $em->persist($logEntry);
        $em->flush();
    }
}
