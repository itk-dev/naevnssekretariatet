<?php

namespace App\Repository;

use App\Entity\CaseEntity;
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
}
