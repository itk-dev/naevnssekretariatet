<?php

namespace App\Repository;

use App\Entity\DigitalPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DigitalPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalPost[]    findAll()
 * @method DigitalPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalPost::class);
    }
}
