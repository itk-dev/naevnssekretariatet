<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\CaseEvent;
use App\Entity\CaseEventPartyRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function findByCase(CaseEntity $case, array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $criteria['caseEntity'] = $case;

        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function createAvailableCaseEventsForCaseQueryBuilder($alias, CaseEntity $caseEntity): QueryBuilder
    {
        $relationAlias = $alias.'_relation';
        $partyAlias = $relationAlias.'_part';

        return $this->createQueryBuilder($alias)
            ->where($alias.'.caseEntity = :case')
            ->setParameter('case', $caseEntity->getId(), 'uuid')
            ->leftJoin(CaseEventPartyRelation::class, $relationAlias, Join::WITH, $alias.'.id = '.$relationAlias.'.caseEvent')
            ->leftJoin($relationAlias.'.party', $partyAlias)
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
