<?php

namespace App\Logging\EntityListener;

use App\Entity\CasePartyRelation;
use App\Service\CaseManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class CasePartyRelationListener extends AbstractEntityListener
{
    /**
     * @var CaseManager
     */
    private $caseManager;

    public function __construct(CaseManager $caseManager, Security $security)
    {
        $this->caseManager = $caseManager;
        parent::__construct($security);
    }

    public function postUpdate(CasePartyRelation $relation, LifecycleEventArgs $args)
    {
        $this->caseManager->updateSortingProperties($relation->getCase());
        $this->logActivity('Updated party relation', $args);
    }

    public function postPersist(CasePartyRelation $relation, LifecycleEventArgs $args)
    {
        $this->caseManager->updateSortingProperties($relation->getCase());
        $this->logActivity('Added party', $args);
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
