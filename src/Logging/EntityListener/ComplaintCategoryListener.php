<?php

namespace App\Logging\EntityListener;

use App\Entity\ComplaintCategory;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class ComplaintCategoryListener extends AbstractRelatedToCaseListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevGetFunctionNotFoundException
     * @throws ItkDevLoggingException
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function postUpdate(ComplaintCategory $complaintCategory, LifecycleEventArgs $args)
    {
        $this->logActivity('Complaint category updated', $args);
    }
}
