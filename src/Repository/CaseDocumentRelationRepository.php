<?php

namespace App\Repository;

use App\Entity\CaseDocumentRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseDocumentRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseDocumentRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseDocumentRelation[]    findAll()
 * @method CaseDocumentRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseDocumentRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseDocumentRelation::class);
    }

    // /**
    //  * @return CaseDocumentRelation[] Returns an array of CaseDocumentRelation objects
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
    public function findOneBySomeField($value): ?CaseDocumentRelation
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
