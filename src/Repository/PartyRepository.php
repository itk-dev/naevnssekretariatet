<?php

namespace App\Repository;

use App\Entity\CasePartyRelation;
use App\Entity\Party;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Party|null find($id, $lockMode = null, $lockVersion = null)
 * @method Party|null findOneBy(array $criteria, array $orderBy = null)
 * @method Party[]    findAll()
 * @method Party[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Party::class);
    }

    public function findPartyByCaseIdAndIdentification(Uuid $caseId, string $identification)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('p')
            ->join(CasePartyRelation::class, 'cp')
            ->where('cp.party = p.id')
            ->andWhere('cp.case = :case_id')
            ->setParameter('case_id', $caseId->toBinary())
            ->andWhere('p.identification.identifier = :identification')
            ->setParameter('identification', $identification)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
