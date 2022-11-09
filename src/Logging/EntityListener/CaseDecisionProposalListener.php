<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseDecisionProposal;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class CaseDecisionProposalListener extends AbstractEntityListener
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
    public function postUpdate(CaseDecisionProposal $caseDecisionProposal, LifecycleEventArgs $args)
    {
        $this->logActivity('Case updated', $args);
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

        /** @var CaseDecisionProposal $caseDecisionProposal */
        $caseDecisionProposal = $args->getObject();

        $logEntry = $this->createLogEntry($action, $caseDecisionProposal->getCaseEntity(), $args);
        $em->persist($logEntry);
        $em->flush();
    }
}
