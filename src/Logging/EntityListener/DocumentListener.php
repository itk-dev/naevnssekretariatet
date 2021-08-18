<?php

namespace App\Logging\EntityListener;

use App\Entity\Document;
use App\Logging\ItkDevGetFunctionNotFoundException;
use App\Logging\ItkDevLoggingException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\Security;

class DocumentListener extends AbstractRelatedToCaseListener
{
    public function __construct(Security $security)
    {
        parent::__construct($security);
    }

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     * @throws ItkDevGetFunctionNotFoundException
     */
    public function postPersist(Document $document, LifecycleEventArgs $args)
    {
        $this->logActivity('Upload', $args);
    }

}
