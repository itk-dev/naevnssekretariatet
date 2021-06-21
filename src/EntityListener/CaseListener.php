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

        $dataArray = array();

        foreach (array_keys($changeArray) as $array_key) {
            switch ($array_key){
                case "board":
                case "municipality":
                    $dataArray[$array_key] = $changeArray[$array_key][1]->getName();
                    break;
                case "createdAt":
                    $date = $changeArray[$array_key][1];
                    $dataArray[$array_key] = $date->format('d-m-Y H:i');
                    break;
                default:
                    if (null === $changeArray[$array_key][1]) {
                        break;
                    }
                    $dataArray[$array_key] = $changeArray[$array_key][1];
                    break;
            }
        }

        $logEntry = new LogEntry();
        $logEntry->setCaseID($caseID);
        $logEntry->setEntity('Case');
        $logEntry->setEntityID($caseID);
        $logEntry->setAction($action);
        $logEntry->setUser('tester');
        // todo set user on LogEntry
        $logEntry->setData(json_encode($dataArray));

        return $logEntry;
    }
}
