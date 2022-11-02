<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseEntity;
use App\Entity\ComplaintCategory;
use App\Entity\LogEntry;
use App\Entity\User;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

abstract class AbstractEntityListener
{
    public function __construct(private Security $security)
    {
    }

    abstract public function logActivity(string $action, LifecycleEventArgs $args): void;

    /**
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ItkDevLoggingException
     */
    public function createLogEntry(string $action, CaseEntity $case, LifecycleEventArgs $args): LogEntry
    {
        $em = $args->getEntityManager();

        $object = $args->getObject();

        $isRemoveAction = false;

        $changeArray = $em->getUnitOfWork()->getEntityChangeSet($object);

        // If empty then it must be remove/delete action
        if (empty($changeArray)) {
            $changeArray = $em->getUnitOfWork()->getOriginalEntityData($object);
            $isRemoveAction = true;
        }

        // Array with change data
        $dataArray = [];

        // Check for collection updates, specifically complaint categories,
        // and compute array containing just the names of the removed/inserted complaint categories.
        foreach ($em->getUnitOfWork()->getScheduledCollectionUpdates() as $collectionUpdate) {
            /** @var $collectionUpdate PersistentCollection */
            $removals = $collectionUpdate->getDeleteDiff();

            $complaintCategoryRemovals = array_map(static function (ComplaintCategory $complaintCategory) {
                return $complaintCategory->getName();
            }, array_filter($removals, static function ($removal) {
                return $removal instanceof ComplaintCategory;
            }));

            if ($complaintCategoryRemovals) {
                $dataArray['Complaint category removals'] = $complaintCategoryRemovals;
            }

            $inserts = $collectionUpdate->getInsertDiff();

            $complaintCategoryInserts = array_map(static function (ComplaintCategory $complaintCategory) {
                return $complaintCategory->getName();
            }, array_filter($inserts, static function ($insert) {
                return $insert instanceof ComplaintCategory;
            }));

            if ($complaintCategoryInserts) {
                $dataArray['Complaint category inserts'] = $complaintCategoryInserts;
            }
        }

        foreach ($changeArray as $key => $value) {
            $changedValue = null;

            if ($isRemoveAction) {
                $changedValue = $value;
            } else {
                if (!array_key_exists(1, $value)) {
                    $message = 'Value array does not contain new value.';
                    throw new ItkDevLoggingException($message);
                }

                $changedValue = $value[1];
            }

            // In case a nullable property is edited to null we must log this
            if (null === $changedValue) {
                $dataArray[$key] = '';
                continue;
            }

            // ID is already present as entity_id no need to add it twice
            if ($changedValue instanceof UuidV4) {
                continue;
            }

            // Handle DateTime(s)
            if ($changedValue instanceof \DateTimeInterface) {
                $dataArray[$key] = $changedValue->format('d-m-Y H:i:s');
                continue;
            }

            // Handle loggable entities
            if ($changedValue instanceof LoggableEntityInterface) {
                $dataArray[$key] = $this->handleLoggableEntities($changedValue);
                continue;
            }

            if (is_scalar($changedValue) || is_array($changedValue)) {
                $dataArray[$key] = $changedValue;
                continue;
            }

            // Logging is done on case level, no need to log case 'twice'
            if ($changedValue instanceof CaseEntity) {
                continue;
            }

            if ($changedValue instanceof PersistentCollection) {
                continue;
            }

            // Property was not handled
            $message = sprintf('Unhandled property %s of type %s.', $key, is_scalar($changedValue) ? gettype($changedValue) : (is_array($changedValue) ? 'array' : get_class($changedValue)));
            throw new ItkDevLoggingException($message);
        }

        // Create log entry
        $logEntry = new LogEntry();

        // Set values on log entry
        $logEntry->setCaseID($case->getId());
        $logEntry->setEntity(get_class($object));
        $logEntry->setEntityID($object->getId());
        $logEntry->setAction($action);

        /** @var User $user */
        $user = $this->security->getUser();
        if (null === $user) {
            $logEntry->setUser('Fixtures');
        } else {
            $logEntry->setUser($user->getName());
        }

        $logEntry->setData($dataArray);

        return $logEntry;
    }

    /**
     * @throws ItkDevGetFunctionNotFoundException
     */
    public function handleLoggableEntities(LoggableEntityInterface $entity): array
    {
        $loggedProperties = $entity->getLoggableProperties();

        $valuesToLog = [];

        foreach ($loggedProperties as $loggedProperty) {
            $nameOfGetter = 'get'.ucfirst($loggedProperty);

            if (!method_exists($entity, $nameOfGetter)) {
                $message = sprintf('Getter %s not found in %s.', $nameOfGetter, get_class($entity));
                throw new ItkDevGetFunctionNotFoundException($message);
            }

            $valuesToLog[$loggedProperty] = call_user_func([
                $entity,
                $nameOfGetter,
            ]);
        }

        return $valuesToLog;
    }
}
