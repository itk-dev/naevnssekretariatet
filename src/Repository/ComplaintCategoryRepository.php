<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\ComplaintCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComplaintCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComplaintCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComplaintCategory[]    findAll()
 * @method ComplaintCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplaintCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComplaintCategory::class);
    }

    public function findComplaintCategoriesByBoard(Board $board)
    {
        return $this->createQueryBuilder('cc')
            ->where(':board MEMBER OF cc.boards')
            ->setParameter(':board', $board->getId()->toBinary())
            ->orderBy('cc.name', 'ASC')
            ->getQuery()->getResult()
        ;
    }

    public function findOneByNameAndBoard(string $name, Board $board): ?ComplaintCategory
    {
        return $this->createQueryBuilder('cc')
            ->where(':board MEMBER OF cc.boards')
            ->setParameter(':board', $board->getId()->toBinary())
            ->andWhere('cc.name = :complaintName')
            ->setParameter('complaintName', $name)
            ->getQuery()->getOneOrNullResult()
        ;
    }
}
