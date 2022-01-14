<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogEntry[]    findAll()
 * @method LogEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function findPrevious(CaseEntity $case, LogEntry $logEntry): ?LogEntry
    {
        return $this->createQueryBuilder('e')
            ->where('e.caseID = :case_id')
            ->setParameter('case_id', $case->getId())
            ->andWhere('e.id != :log_entry_id')
            ->setParameter('log_entry_id', $logEntry->getId()->toBinary())
            ->andWhere('e.createdAt <= :log_entry_createdAt')
            ->setParameter('log_entry_createdAt', $logEntry->getCreatedAt())
            ->orderBy('e.createdAt', Criteria::DESC)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function findNext(CaseEntity $case, LogEntry $logEntry): ?LogEntry
    {
        return $this->createQueryBuilder('e')
            ->where('e.caseID = :case_id')
            ->setParameter('case_id', $case->getId())
            ->andWhere('e.id != :log_entry_id')
            ->setParameter('log_entry_id', $logEntry->getId()->toBinary())
            ->andWhere('e.createdAt >= :log_entry_createdAt')
            ->setParameter('log_entry_createdAt', $logEntry->getCreatedAt())
            ->orderBy('e.createdAt', Criteria::ASC)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
