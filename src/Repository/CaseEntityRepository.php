<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\Municipality;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseEntity[]    findAll()
 * @method CaseEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseEntity::class);
    }

    public function findLatestCaseByMunicipality(Municipality $municipality): ?CaseEntity
    {
        return $this->createQueryBuilder('c')
            ->where('c.municipality = :municipality')
            ->setParameter('municipality', $municipality->getId()->toBinary())
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findReadyCasesWithoutActiveAgendaByBoard(Board $board)
    {
        $activeCases = $this->findCasesWithActiveAgenda();

        $binaryIdsOfActiveCases = array_map(function (CaseEntity $case) {
            return $case->getId()->toBinary();
        }, $activeCases);

        $qb = $this->createQueryBuilder('c');

        $qb->select('c')
            ->where('c.board = :board')
            ->setParameter('board', $board->getId()->toBinary())
            ->andWhere('c.isReadyForAgenda = :isReadyForAgendaCheck')
            ->setParameter('isReadyForAgendaCheck', true)
            ->andWhere('c NOT IN (:cases_with_active_agenda)')
            ->setParameter('cases_with_active_agenda', $binaryIdsOfActiveCases)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCasesWithActiveAgenda()
    {
        $qb = $this->createQueryBuilder('c');

        $qb->leftJoin('c.agendaCaseItems', 'aci')
            ->join('aci.agenda', 'a')
            ->where('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
        ;

        return $qb->getQuery()->getResult();
    }
}
