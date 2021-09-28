<?php

namespace App\Repository;

use App\Entity\BoardRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoardRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoardRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoardRole[]    findAll()
 * @method BoardRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoardRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardRole::class);
    }
}
