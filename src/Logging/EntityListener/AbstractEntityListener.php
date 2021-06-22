<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseEntity;
use App\Entity\LogEntry;
use App\Logging\ItkDevLoggingException;
use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

abstract class AbstractEntityListener
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        // Create LogEntry entity
        $logEntry = $this->createLogEntry($action, $args);

        // Persist LogEntry to EntityManager
        $em->persist($logEntry);
        $em->flush();
    }

    /**
     * @throws ItkDevLoggingException
     */
    public function createLogEntry(string $action, LifecycleEventArgs $args): LogEntry
    {
        $em = $args->getEntityManager();

        /** @var CaseEntity $case */
        $case = $args->getObject();
        $changeArray = $em->getUnitOfWork()->getEntityChangeSet($case);

        $dataArray = [];

        foreach ($changeArray as $key => $value) {
            if (!array_key_exists(1, $value)) {
                $message = 'Value array does not contain new value.';
                throw new ItkDevLoggingException($message);
            }

            $changedValue = $value[1];

            // We do not log properties with value null
            // todo determine if we should
            if (null === $changedValue) {
                continue;
            }

            if ($changedValue instanceof UuidV4) {
                $dataArray[$key] = $changedValue->__toString();
                continue;
            }

            if ($changedValue instanceof \DateTime) {
                $dataArray[$key] = $changedValue->format('d-m-Y H:i:s');
                continue;
            }

            if ($changedValue instanceof LoggableEntityInterface) {
                $dataArray[$key] = $this->handleLoggableEntities($changedValue);
                continue;
            }

            if (is_scalar($changedValue)) {
                $dataArray[$key] = $changedValue;
                continue;
            }

            $message = sprintf('Unhandled property %s of type %s.', $key, get_class($changedValue));
            throw new ItkDevLoggingException($message);
        }

        $logEntry = new LogEntry();

        $logEntry->setCaseID($case->getId());
        $logEntry->setEntity(get_class($case));
        $logEntry->setEntityID($case->getId());
        $logEntry->setAction($action);
        $logEntry->setUser($this->security->getUser()->getUsername());
        $logEntry->setData($dataArray);

        return $logEntry;
    }

    /**
     * @throws ItkDevLoggingException
     */
    private function handleLoggableEntities(LoggableEntityInterface $entity): array
    {
        $loggedProperties = $entity->getLoggableProperties();

        $valueLog = [];

        foreach ($loggedProperties as $loggedProperty) {
            $nameOfGetter = 'get'.ucfirst($loggedProperty);

            if (!method_exists($entity, $nameOfGetter)) {
                $message = sprintf('Getter %s not found in %s.', $nameOfGetter, get_class($entity));
                throw new ItkDevLoggingException($message);
            }

            $valueLog[$loggedProperty] = call_user_func([
                $entity,
                $nameOfGetter,
            ]);
        }

        return $valueLog;
    }
}