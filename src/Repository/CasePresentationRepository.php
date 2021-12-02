<?php

namespace App\Repository;

use App\Entity\CasePresentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CasePresentation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CasePresentation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CasePresentation[]    findAll()
 * @method CasePresentation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CasePresentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CasePresentation::class);
    }
}
