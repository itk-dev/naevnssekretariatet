<?php

namespace App\Repository;

use App\Entity\HearingPostResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HearingPostResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingPostResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingPostResponse[]    findAll()
 * @method HearingPostResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingPostResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingPostResponse::class);
    }
}
