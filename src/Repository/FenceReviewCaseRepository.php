<?php

namespace App\Repository;

use App\Entity\FenceReviewCase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FenceReviewCase|null find($id, $lockMode = null, $lockVersion = null)
 * @method FenceReviewCase|null findOneBy(array $criteria, array $orderBy = null)
 * @method FenceReviewCase[]    findAll()
 * @method FenceReviewCase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FenceReviewCaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FenceReviewCase::class);
    }

    // /**
    //  * @return FenceReviewCase[] Returns an array of FenceReviewCase objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FenceReviewCase
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
