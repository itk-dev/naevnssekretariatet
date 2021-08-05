<?php

namespace App\Logging\EntityListener;

use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

abstract class AbstractRelatedToCaseListener extends AbstractEntityListener
{
    /**
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ItkDevLoggingException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        $object = $args->getObject();

        foreach ($object->getCaseEntities() as $case) {
            $logEntry = $this->createLogEntry($action, $case, $args);
            $em->persist($logEntry);
        }

        $em->flush();
    }
}
