<?php

namespace App\Monolog;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\Security;

class DoctrineHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    protected function write(array $record): void
    {
        $logEntry = new LogEntry();
        $logEntry->setMessage($record['message']);
        $logEntry->setContext($record['context']);
        $logEntry->setLevel($record['level']);
        $logEntry->setLevelName($record['level_name']);
        $logEntry->setChannel($record['channel']);
        $logEntry->setExtra($record['extra']);
        $logEntry->setFormatted($record['formatted']);

        $user = $this->security->getUser();

        if ($user instanceof User) {
            $logEntry->setUser($user);
        }

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }
}
