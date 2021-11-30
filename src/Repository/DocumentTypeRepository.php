<?php

namespace App\Repository;

use App\Entity\UploadedDocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UploadedDocumentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method UploadedDocumentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method UploadedDocumentType[]    findAll()
 * @method UploadedDocumentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UploadedDocumentType::class);
    }
}
