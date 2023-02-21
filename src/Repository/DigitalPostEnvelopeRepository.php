<?php

namespace App\Repository;

use App\Entity\DigitalPost;
use App\Entity\DigitalPostEnvelope;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DigitalPostEnvelope>
 *
 * @method DigitalPostEnvelope|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalPostEnvelope|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalPostEnvelope[]    findAll()
 * @method DigitalPostEnvelope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method DigitalPostEnvelope|null findOneByDigitalPost(DigitalPost $digitalPost)
 */
class DigitalPostEnvelopeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalPostEnvelope::class);
    }

    public function save(DigitalPostEnvelope $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DigitalPostEnvelope $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
