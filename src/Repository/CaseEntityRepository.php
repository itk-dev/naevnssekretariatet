<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Entity\User;
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

    public function findLatestCase(): ?CaseEntity
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
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

    public function findCountOfCasesWithUserAndWithActiveAgenda(User $user)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.assignedTo = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->leftJoin('c.agendaCaseItems', 'aci')
            ->join('aci.agenda', 'a')
            ->andWhere('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithUserAndSomeExceededDeadline(User $user)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.assignedTo = :user')
            ->setParameter('user', $user->getId()->toBinary())
            ->andWhere('c.hasReachedHearingDeadline = :isExceeded OR c.hasReachedProcessingDeadline = :isExceeded')
            ->setParameter('isExceeded', true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithActiveAgendaByBoard(Board $board)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.board = :board')
            ->setParameter('board', $board->getId()->toBinary())
            ->leftJoin('c.agendaCaseItems', 'aci')
            ->join('aci.agenda', 'a')
            ->andWhere('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithSomeExceededDeadlineByBoard(Board $board)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.board = :board')
            ->setParameter('board', $board->getId()->toBinary())
            ->andWhere('c.hasReachedHearingDeadline = :isExceeded OR c.hasReachedProcessingDeadline = :isExceeded')
            ->setParameter('isExceeded', true);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
