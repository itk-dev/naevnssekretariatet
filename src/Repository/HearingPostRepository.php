<?php

namespace App\Repository;

use App\Entity\HearingPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HearingPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingPost[]    findAll()
 * @method HearingPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingPost::class);
    }
}
