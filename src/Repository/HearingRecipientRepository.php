<?php

namespace App\Repository;

use App\Entity\HearingRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HearingRecipient>
 *
 * @method HearingRecipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingRecipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingRecipient[]    findAll()
 * @method HearingRecipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingRecipient::class);
    }

    public function add(HearingRecipient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HearingRecipient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
