<?php

namespace App\Repository;

use App\Entity\SubBoard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubBoard|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubBoard|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubBoard[]    findAll()
 * @method SubBoard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubBoardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubBoard::class);
    }
}
