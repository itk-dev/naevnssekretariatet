<?php

namespace App\Repository;

use App\Entity\CasePartyRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CasePartyRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CasePartyRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CasePartyRelation[]    findAll()
 * @method CasePartyRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CasePartyRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CasePartyRelation::class);
    }

    public function findTenants($case): ?array
    {
        return $this->createQueryBuilder('r')
            ->where('r.case = :case')
            ->setParameter('case', $case)
            ->andWhere('r.type = :tenant')
            ->setParameter('tenant', 'Tenant')
            ->getQuery()
            ->execute()
        ;
    }
}
