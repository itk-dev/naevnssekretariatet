<?php

namespace App\Logging\EntityListener;

use App\Entity\CasePresentation;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class CasePresentationListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(CasePresentation $casePresentation, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
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

        /** @var CasePresentation $casePresentation */
        $casePresentation = $args->getObject();

        $logEntry = $this->createLogEntry($action, $casePresentation->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
