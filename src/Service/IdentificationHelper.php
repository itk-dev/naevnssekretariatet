<?php

namespace App\Service;

use App\Entity\CaseEntity;
use App\Entity\Embeddable\Identification;
use App\Exception\CprException;
use App\Exception\CvrException;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class IdentificationHelper implements EventSubscriberInterface
{
    public const IDENTIFIER_TYPE_CPR = 'CPR';
    public const IDENTIFIER_TYPE_CVR = 'CVR';

    public function __construct(private CprHelper $cprHelper, private CvrHelper $cvrHelper, private PropertyAccessorInterface $propertyAccessor)
    {
    }

    /**
     * @throws CprException
     * @throws CvrException
     */
    public function validateIdentification(CaseEntity $case, string $idProperty, string $addressProperty, string $nameProperty)
    {
        /** @var Identification $identification */
        $identification = $this->propertyAccessor->getValue($case, $idProperty);

        if (self::IDENTIFIER_TYPE_CPR === $identification->getType()) {
            $this->cprHelper->validateCpr($case, $idProperty, $addressProperty, $nameProperty);
        } else {
            $this->cvrHelper->validateCvr($case, $idProperty, $addressProperty, $nameProperty);
        }
    }

    public function fetchIdentifierData(string $identifier, string $type): JsonResponse
    {
        try {
            $data = self::IDENTIFIER_TYPE_CPR == $type ? $this->cprHelper->lookupCPR($identifier) : $this->cvrHelper->lookupCvr($identifier);
        } catch (CprException|CvrException) {
            return new JsonResponse();
        }

        $dataArray = json_decode(json_encode($data), true);

        $relevantData = self::IDENTIFIER_TYPE_CPR == $type ? $this->cprHelper->collectRelevantData($dataArray) : $this->cvrHelper->collectRelevantData($dataArray);

        return new JsonResponse($relevantData);
    }

    /**
     * Set Identification.validatedAt to null when changing relevant properties.
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $object = $args->getObject();
        $changeSet = $args->getEntityChangeSet();
        foreach ($changeSet as $propertyPath => $value) {
            if (!$object instanceof CaseEntity) {
                continue;
            }

            // Check that changed property warrants a change of bringer validatedAt
            foreach ($object->getIdentificationInvalidationProperties() as $identificationProperty => $invalidationProperties) {
                if (in_array($propertyPath, $invalidationProperties) && !str_contains($propertyPath, 'validatedAt')) {
                    /** @var Identification $id */
                    $id = $this->propertyAccessor->getValue($object, $identificationProperty);
                    $id->setValidatedAt(null);
                }
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
