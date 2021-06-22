<?php

namespace App\Logging\EntityListener;

use App\Entity\Board;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class BoardListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(Board $board, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }
}
