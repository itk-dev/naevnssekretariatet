<?php

namespace App\Repository;

use App\Entity\Hearing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hearing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hearing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hearing[]    findAll()
 * @method Hearing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hearing::class);
    }
}
