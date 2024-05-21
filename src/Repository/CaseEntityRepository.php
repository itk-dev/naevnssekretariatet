<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\CaseEntity;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
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

        // We filter out finished cases, i.e. with last status in board.
        $statuses = explode(PHP_EOL, $board->getStatuses());
        $endStatus = end($statuses);

        $qb->select('c')
            ->where('c.board = :board')
            ->setParameter('board', $board->getId()->toBinary())
            ->andWhere('c.isReadyForAgenda = :isReadyForAgendaCheck')
            ->setParameter('isReadyForAgendaCheck', true)
            ->andWhere('c.currentPlace != :end_status')
            ->setParameter('end_status', $endStatus)
        ;

        // If $binaryIdsOfActiveCases array is empty NOT IN does not behave as expected
        if (count($binaryIdsOfActiveCases) > 0) {
            $qb
                ->andWhere('c NOT IN (:cases_with_active_agenda)')
                ->setParameter('cases_with_active_agenda', $binaryIdsOfActiveCases)
            ;
        }

        $qb->orderBy('c.caseNumber', 'ASC');

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
    public function findCountOfCasesAndWithActiveAgendaBy(array $criteria, bool $getActive = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->leftJoin('c.agendaCaseItems', 'aci')
            ->join('aci.agenda', 'a')
            ->where('a.status != :agenda_status')
            ->setParameter('agenda_status', AgendaStatus::FINISHED)
        ;

        $this->applyCriteriaToQueryBuilder($qb, $criteria);

        $qb->andWhere($this->getExprWithBoardFinishStatuses($qb, $getActive));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Assumes criteria values has ID.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findCountOfCasesWithSomeExceededDeadlineBy(array $criteria, bool $getActive = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->where('c.hasReachedHearingDeadline = :isExceeded OR c.hasReachedProcessingDeadline = :isExceeded OR c.hasReachedHearingResponseDeadline = :isExceeded')
            ->setParameter('isExceeded', true)
        ;

        $this->applyCriteriaToQueryBuilder($qb, $criteria);

        $qb->andWhere($this->getExprWithBoardFinishStatuses($qb, $getActive));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithActiveHearingBy(array $criteria, bool $getActive = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->join('c.hearing', 'h')
            ->where('h.startedOn IS NOT NULL')
            ->andWhere('h.finishedOn IS NULL')
        ;

        $this->applyCriteriaToQueryBuilder($qb, $criteria);

        $qb->andWhere($this->getExprWithBoardFinishStatuses($qb, $getActive));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithNewHearingPostBy(array $criteria, bool $getActive = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->join('c.hearing', 'h')
            ->where('h.hasNewHearingPost = 1')
        ;

        $this->applyCriteriaToQueryBuilder($qb, $criteria);

        $qb->andWhere($this->getExprWithBoardFinishStatuses($qb, $getActive));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function createQueryBuilderForBoardMember(BoardMember $boardMember): QueryBuilder
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->leftJoin('c.agendaCaseItems', 'aci')
            ->leftJoin('aci.agenda', 'a')
            ->where(':boardMember MEMBER OF a.boardmembers')
            ->setParameter('boardMember', $boardMember->getId()->toBinary())
        ;

        // The status that is considered finished may vary from board to board
        $qb->orWhere($this->getExprWithBoardFinishStatuses($qb, false));

        return $qb;
    }

    public function updateQueryBuilderForBoardMember(BoardMember $boardMember, QueryBuilder $qb): QueryBuilder
    {
        $qb->leftJoin('c.agendaCaseItems', 'aci')
            ->leftJoin('aci.agenda', 'a')
        ;

        $orExp = $qb->expr()->orX();

        $orExp->add($qb->expr()->isMemberOf(':boardMember', 'a.boardmembers'));
        $qb->setParameter('boardMember', $boardMember->getId()->toBinary());

        $orExp->add($this->getExprWithBoardFinishStatuses($qb, false));

        $qb->andWhere($orExp);

        return $qb;
    }

    public function getExprWithBoardFinishStatuses(QueryBuilder $qb, bool $getActive = true): Expr\Orx
    {
        $boardExpression = $qb->expr()->orX();
        $boardRepository = $this->getEntityManager()->getRepository(Board::class);
        $boards = $boardRepository->findAll();

        $count = 0;
        foreach ($boards as $board) {
            // Construct different variable names for each board
            $statusDQLVariable = 'board_finish_status_'.$count;
            $boardDQLVariable = 'board_'.$count;

            $boardExpression->add($qb->expr()->andX(
                $getActive
                    ? $qb->expr()->neq('c.currentPlace', ':'.$statusDQLVariable)
                    : $qb->expr()->eq('c.currentPlace', ':'.$statusDQLVariable),
                $qb->expr()->eq('c.board', ':'.$boardDQLVariable),
            ));

            $qb
                ->setParameter($statusDQLVariable, $board->getFinalStatus())
                ->setParameter($boardDQLVariable, $board->getId()->toBinary())
            ;
            ++$count;
        }

        return $boardExpression;
    }

    public function findCountOfCases(array $criteria, bool $getActive = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)');

        $this->applyCriteriaToQueryBuilder($qb, $criteria);

        $qb->andWhere($this->getExprWithBoardFinishStatuses($qb, $getActive));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function applyCriteriaToQueryBuilder(QueryBuilder $qb, array $criteria)
    {
        foreach ($criteria as $key => $value) {
            // TODO: Update beneath to include objects without an id, e.g. scalar types
            $parameterValue = $value->getId()->toBinary();
            $parameterName = uniqid($key);
            $qb->andWhere('c.'.$key.'= :'.$parameterName)
                ->setParameter($parameterName, $parameterValue)
            ;
        }
    }

    public function findNonFinishedCasesInSameBoard(CaseEntity $case, string $endStatus)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->where('c.board = :board')
            ->setParameter('board', $case->getBoard()->getId(), 'uuid')
            ->andWhere('c.currentPlace != :end_status')
            ->setParameter('end_status', $endStatus)
            ->orderBy('c.caseNumber', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
