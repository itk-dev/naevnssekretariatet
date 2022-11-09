<?php

namespace App\Repository;

use App\Entity\InspectionLetter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InspectionLetter|null find($id, $lockMode = null, $lockVersion = null)
 * @method InspectionLetter|null findOneBy(array $criteria, array $orderBy = null)
 * @method InspectionLetter[]    findAll()
 * @method InspectionLetter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InspectionLetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InspectionLetter::class);
    }
}
