<?php

namespace App\Logging\EntityListener;

use App\Entity\Municipality;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class MunicipalityListener extends AbstractEntityListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function postUpdate(Municipality $municipality, LifecycleEventArgs $args)
    {
        $this->logActivity('Update', $args);
    }
}
