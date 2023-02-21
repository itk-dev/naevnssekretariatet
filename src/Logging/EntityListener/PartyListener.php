<?php

namespace App\Logging\EntityListener;

use App\Entity\Party;
use App\Repository\CasePartyRelationRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class PartyListener extends AbstractEntityListener
{
    public function __construct(private readonly CasePartyRelationRepository $relationRepository, Security $security)
    {
        parent::__construct($security);
    }

    public function postUpdate(Party $party, LifecycleEventArgs $args)
    {
        $this->logActivity('Party updated', $args);
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
