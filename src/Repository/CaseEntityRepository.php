<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\Municipality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseEntity[]    findAll()
 * @method CaseEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseEntity::class);
    }

    public function findLatestCase(Municipality $municipality): ?CaseEntity
    {
        return $this->createQueryBuilder('c')
            ->where('c.municipality = :municipality')
            ->setParameter('municipality', $municipality->getId()->toBinary())
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
