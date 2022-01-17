<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\CaseEntity;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * Assumes criteria values has ID.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findCountOfCasesAndWithActiveAgendaBy(array $criteria): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->leftJoin('c.agendaCaseItems', 'aci')
            ->join('aci.agenda', 'a')
            ->where('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
        ;

        foreach ($criteria as $key => $value) {
            // TODO: Update beneath to include objects without an id, e.g. scalar types
            $parameterValue = $value->getId()->toBinary();
            $parameterName = uniqid($key);
            $qb->andWhere('c.'.$key.'= :'.$parameterName)
                ->setParameter($parameterName, $parameterValue)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Assumes criteria values has ID.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findCountOfCasesWithSomeExceededDeadlineBy(array $criteria): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.hasReachedHearingDeadline = :isExceeded OR c.hasReachedProcessingDeadline = :isExceeded')
            ->setParameter('isExceeded', true)
        ;

        foreach ($criteria as $key => $value) {
            // TODO: Update beneath to include objects without an id, e.g. scalar types
            $parameterValue = $value->getId()->toBinary();
            $parameterName = uniqid($key);
            $qb->andWhere('c.'.$key.'= :'.$parameterName)
                ->setParameter($parameterName, $parameterValue)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithActiveHearingBy(array $criteria): int
    {
        // TODO: Update beneath when hearing stuff has been implemented
        return -1;
    }

    public function findCountOfCasesWithNewHearingPostBy(array $criteria): int
    {
        // TODO: Update beneath when hearing stuff has been implemented
        return -1;
    }
}
