<?php

namespace App\Monolog;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Security\Core\Security;

class DoctrineHandler extends AbstractProcessingHandler
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly Security $security, $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
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
