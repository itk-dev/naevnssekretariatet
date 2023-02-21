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
        return $this->findRelation($case, $logEntry, 'previous');
    }

    public function findNext(CaseEntity $case, LogEntry $logEntry): ?LogEntry
    {
        return $this->findRelation($case, $logEntry, 'next');
    }

    public function findRelation(CaseEntity $case, LogEntry $logEntry, string $relation): ?LogEntry
    {
        return match ($relation) {
            'previous', 'next' => $this->createQueryBuilder('e')
                ->where('e.caseID = :case_id')
                ->setParameter('case_id', $case->getId())
                // The id is a ULID (Universally Unique Lexicographically Sortable Identifier) (cf. https://symfony.com/doc/current/components/uid.html#ulids)
                ->andWhere(sprintf('e.id %s :log_entry_id', 'next' === $relation ? '>' : '<'))
                ->setParameter('log_entry_id', $logEntry->getId()->toBinary())
                ->orderBy('e.id', 'next' === $relation ? Criteria::ASC : Criteria::DESC)
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult(),
            default => throw new \InvalidArgumentException(sprintf('Unknown relation: %s', $relation)),
        };
    }
}
