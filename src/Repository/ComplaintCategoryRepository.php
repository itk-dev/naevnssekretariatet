<?php

namespace App\Repository;

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

    // /**
    //  * @return ComplaintCategory[] Returns an array of ComplaintCategory objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ComplaintCategory
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
