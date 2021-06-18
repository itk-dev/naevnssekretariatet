<?php

namespace App\EntityListener;

use App\Entity\CaseEntity;
use App\Entity\LogEntry;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class CaseListener
{
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function postUpdate(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function postPersist(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Create', $args);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        // Create LogEntry entity
        $logEntry = $this->createLogEntry($action, $args);

        // Persist LogEntry to EntityManager

        $em->persist($logEntry);
        $em->flush();
    }

    public function createLogEntry(string $action, LifecycleEventArgs $args): LogEntry
    {
        $em = $args->getEntityManager();

        /** @var CaseEntity $case */
        $case = $args->getObject();
        $caseID = $case->getId();
        $changeArray = $em->getUnitOfWork()->getEntityChangeSet($args->getObject());

        $logEntry = new LogEntry();
        $logEntry->setCaseID($caseID);
        $logEntry->setEntity('Case');
        $logEntry->setEntityID($caseID);
        $logEntry->setAction($action);
        // todo set user on LogEntry
        $logEntry->setData('test');
        // todo figure out how to fill data property
        //$logEntry->setData(json_encode($changeArray));

        return $logEntry;
    }
}
