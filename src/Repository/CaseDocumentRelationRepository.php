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
}
