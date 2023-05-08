<?php

namespace App\Repository;

use App\Entity\HearingBriefingRecipient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HearingBriefingRecipient>
 *
 * @method HearingBriefingRecipient|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingBriefingRecipient|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingBriefingRecipient[]    findAll()
 * @method HearingBriefingRecipient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingBriefingRecipientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingBriefingRecipient::class);
    }

    public function add(HearingBriefingRecipient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HearingBriefingRecipient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return HearingBriefingRecipient[] Returns an array of HearingBriefingRecipient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HearingBriefingRecipient
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
