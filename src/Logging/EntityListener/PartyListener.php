<?php

namespace App\Logging\EntityListener;

use App\Entity\Party;
use App\Repository\CasePartyRelationRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class PartyListener extends AbstractEntityListener
{
    /**
     * @var CasePartyRelationRepository
     */
    private $relationRepository;

    public function __construct(CasePartyRelationRepository $relationRepository, Security $security)
    {
        $this->relationRepository = $relationRepository;
        parent::__construct($security);
    }

    public function postUpdate(Party $party, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }

    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var Party $party */
        $party = $args->getObject();

        // Find relations for this party
        $relations = $this->relationRepository->findBy(['party' => $party]);

        // Create a log entry for each of the cases
        foreach ($relations as $relation) {
            $logEntry = $this->createLogEntry($action, $relation->getCase(), $args);
            $em->persist($logEntry);
        }

        $em->flush();
    }
}