<?php

namespace App\Logging\EntityListener;

use App\Entity\CaseEntity;
use App\Entity\LogEntry;
use App\Logging\EntityListener\AbstractEntityListener;
use App\Logging\ItkDevLoggingException;
use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Uid\UuidV4;

class CaseListener extends AbstractEntityListener
{
    private $security;

    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postPersist(CaseEntity $case, LifecycleEventArgs $args)
    {
        $this->logActivity('Create', $args);
    }
}
