<?php

namespace App\Repository;

use App\Entity\DigitalPostDocument;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DigitalPostDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalPostDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalPostDocument[]    findAll()
 * @method DigitalPostDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalPostDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalPostDocument::class);
    }
}
