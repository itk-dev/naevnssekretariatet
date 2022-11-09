<?php

namespace App\Logging\EntityListener;

use App\Entity\AgendaCaseItem;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class AgendaCaseItemListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws OptimisticLockException
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ORMException
     * @throws ItkDevLoggingException
     */
    public function postPersist(AgendaCaseItem $agendaCaseItem, LifecycleEventArgs $args)
    {
        $this->logActivity('Case created', $args);
    }

    /**
     * @throws OptimisticLockException
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ORMException
     * @throws ItkDevLoggingException
     */
    public function postUpdate(AgendaCaseItem $agendaCaseItem, LifecycleEventArgs $args)
    {
        $this->logActivity('Case updated', $args);
    }

    /**
     * @throws OptimisticLockException
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ORMException
     * @throws ItkDevLoggingException
     */
    public function preRemove(AgendaCaseItem $agendaCaseItem, LifecycleEventArgs $args)
    {
        $this->logActivity('Case removed', $args);
    }

    /**
     * @throws OptimisticLockException
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ORMException
     * @throws ItkDevLoggingException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var AgendaCaseItem $agendaCaseItem */
        $agendaCaseItem = $args->getObject();

        $logEntry = $this->createLogEntry($action, $agendaCaseItem->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
