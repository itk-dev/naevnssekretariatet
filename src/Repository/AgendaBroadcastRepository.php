<?php

namespace App\Repository;

use App\Entity\AgendaBroadcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaBroadcast|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaBroadcast|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaBroadcast[]    findAll()
 * @method AgendaBroadcast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaBroadcastRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaBroadcast::class);
    }
}
