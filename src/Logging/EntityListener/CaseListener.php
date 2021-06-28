<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseEntity;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class CaseListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postPersist(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Create', $args);
    }

    /**
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ItkDevLoggingException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var CaseEntity $case */
        $case = $args->getObject();

        $logEntry = $this->createLogEntry($action, $case, $args);

        $em->persist($logEntry);
        $em->flush();
    }
}
