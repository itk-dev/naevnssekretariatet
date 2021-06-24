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

    /**
     * @throws ItkDevLoggingException
     * @throws ORMException
     */
    public function logActivity(string $action, LifecycleEventArgs $args): void
    {
        $em = $args->getEntityManager();

        /** @var Board $board */
        $board = $args->getObject();

        foreach ($board->getCaseEntities() as $case) {
            $logEntry = $this->createLogEntry($action, $case, $args);
            $em->persist($logEntry);
        }

        $em->flush();
    }
}
