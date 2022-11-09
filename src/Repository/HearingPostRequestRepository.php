<?php

namespace App\Repository;

use App\Entity\HearingPostRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HearingPostRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingPostRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingPostRequest[]    findAll()
 * @method HearingPostRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingPostRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingPostRequest::class);
    }
}
