<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\DigitalPost;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DigitalPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalPost[]    findAll()
 * @method DigitalPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalPost::class);
    }

    public function findByEntity(object $entity, array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $criteria['entityType'] = get_class($entity);
        $criteria['entityId'] = $entity->getId();

        return $this->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findByDocumentAndCase(Document $document, CaseEntity $case, array $criteria = [], array $orderBy = null, $limit = null, $offset = null): array
    {
        $criteria['document'] = $document;

        return $this->findByEntity($case, $criteria, $orderBy, $limit, $offset);
    }
}
