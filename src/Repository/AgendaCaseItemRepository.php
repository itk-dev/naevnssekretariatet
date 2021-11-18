<?php

namespace App\Repository;

use App\Entity\AgendaCaseItem;
use App\Entity\CaseEntity;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaCaseItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaCaseItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaCaseItem[]    findAll()
 * @method AgendaCaseItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaCaseItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaCaseItem::class);
    }

    public function findActiveAgendaCaseItemIdsByCase(CaseEntity $case): array
    {
        $qb = $this->createQueryBuilder('aci');

        $qb->join('aci.caseEntity', 'c')
            ->join('aci.agenda', 'a')
            ->where('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
            ->andWhere('c.id = :case_id')
            ->setParameter('case_id', $case->getId()->toBinary())
            ->orderBy('a.date', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findFinishedAgendaCaseItemIdsByCase(CaseEntity $case): array
    {
        $qb = $this->createQueryBuilder('aci');

        $qb->join('aci.caseEntity', 'c')
            ->join('aci.agenda', 'a')
            ->where('a.status = :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
            ->andWhere('c.id = :case_id')
            ->setParameter('case_id', $case->getId()->toBinary())
            ->orderBy('a.date', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
