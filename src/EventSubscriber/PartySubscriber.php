<?php

namespace App\EventSubscriber;

use App\Entity\Party;
use App\Repository\CasePartyRelationRepository;
use App\Service\PartyHelper;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PartySubscriber implements EventSubscriberInterface
{
    /**
     * @var CasePartyRelationRepository
     */
    private $relationRepository;
    /**
     * @var PartyHelper
     */
    private $partyHelper;

    public function __construct(CasePartyRelationRepository $relationRepository, PartyHelper $partyHelper)
    {
        $this->relationRepository = $relationRepository;
        $this->partyHelper = $partyHelper;
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $changeSet = $args->getEntityChangeSet();
        if ($object instanceof Party && array_key_exists('name', $changeSet)) {
            $relations = $this->relationRepository->findBy(['party' => $object->getId()]);

            foreach ($relations as $relation) {
                $relevantComplainant = $this->partyHelper->getSortingRelevantComplainant($relation->getCase());
                $relation->getCase()->setSortingComplainant($relevantComplainant);
                $relevantCounterpart = $this->partyHelper->getSortingRelevantCounterpart($relation->getCase());
                $relation->getCase()->setSortingCounterpart($relevantCounterpart);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }
}
