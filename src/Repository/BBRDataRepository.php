<?php

namespace App\Repository;

use App\Entity\BBRData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BBRData|null find($id, $lockMode = null, $lockVersion = null)
 * @method BBRData|null findOneBy(array $criteria, array $orderBy = null)
 * @method BBRData[]    findAll()
 * @method BBRData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BBRDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BBRData::class);
    }
}
