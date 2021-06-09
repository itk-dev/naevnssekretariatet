<?php

namespace App\Repository;

use App\Entity\ResidentComplaintBoardCase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResidentComplaintBoardCase|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResidentComplaintBoardCase|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResidentComplaintBoardCase[]    findAll()
 * @method ResidentComplaintBoardCase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResidentComplaintBoardCaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResidentComplaintBoardCase::class);
    }
}
