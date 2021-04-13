<?php

namespace App\Repository;

use App\Entity\BoardMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoardMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoardMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoardMember[]    findAll()
 * @method BoardMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoardMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardMember::class);
    }

    // /**
    //  * @return BoardMember[] Returns an array of BoardMember objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BoardMember
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
