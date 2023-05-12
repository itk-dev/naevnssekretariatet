<?php

namespace App\Repository;

use App\Entity\HearingBriefing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HearingBriefing>
 *
 * @method HearingBriefing|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingBriefing|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingBriefing[]    findAll()
 * @method HearingBriefing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingBriefingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingBriefing::class);
    }

    public function save(HearingBriefing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HearingBriefing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
