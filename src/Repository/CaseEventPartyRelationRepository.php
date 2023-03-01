<?php

namespace App\Repository;

use App\Entity\CaseEventPartyRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseEventPartyRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseEventPartyRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseEventPartyRelation[]    findAll()
 * @method CaseEventPartyRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseEventPartyRelationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseEventPartyRelation::class);
    }
}
