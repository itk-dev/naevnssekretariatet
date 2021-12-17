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
        // We allow sort of cases on party names
        // Check that object is Party and name has been changed
        if ($object instanceof Party && array_key_exists('name', $changeSet)) {
            $relations = $this->relationRepository->findBy(['party' => $object->getId()]);

            foreach ($relations as $relation) {
                $this->partyHelper->updateSortingProperties($relation->getCase());
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
