<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseEvent[]    findAll()
 * @method CaseEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseEvent::class);
    }

    public function findByCase(CaseEntity $case, array $criteria = [], ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $criteria['caseEntity'] = $case;

        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function createAvailableCaseEventsForCaseQueryBuilder($alias, CaseEntity $caseEntity): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->join($alias.'.caseEntities', 'c')
            ->where('c.id = :case_id')
            ->setParameter('case_id', $caseEntity->getId(), 'uuid')
            ->orderBy($alias.'.receivedAt', Criteria::DESC)
        ;
    }

    public function getAvailableCaseEventsForCase(CaseEntity $caseEntity)
    {
        return $this->createAvailableCaseEventsForCaseQueryBuilder('ce', $caseEntity)
            ->getQuery()
            ->getResult()
        ;
    }
}
