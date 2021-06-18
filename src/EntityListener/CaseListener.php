<?php

namespace App\EntityListener;

use App\Entity\CaseEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;

class CaseListener
{
    public function postPersist(CaseEntity $case, LifecycleEventArgs $args)
    {
    }
}
