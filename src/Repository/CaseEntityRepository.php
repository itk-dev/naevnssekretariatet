<?php

namespace App\Repository;

use App\Entity\Board;
use App\Entity\BoardMember;
use App\Entity\CaseEntity;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
    public function findCountOfCasesAndWithActiveAgendaBy(array $criteria, bool $getFinished = true): int
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

        $this->updateQueryWithAndContainingBoardOrExpressions($qb, $getFinished);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Assumes criteria values has ID.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function findCountOfCasesWithSomeExceededDeadlineBy(array $criteria, bool $getFinished = true): int
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

        $this->updateQueryWithAndContainingBoardOrExpressions($qb, $getFinished);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithActiveHearingBy(array $criteria, bool $getFinished = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)')
            ->join('c.hearing', 'h')
            ->where('h.startedOn IS NOT NULL')
            ->andWhere('h.finishedOn IS NULL')
        ;

        foreach ($criteria as $key => $value) {
            // TODO: Update beneath to include objects without an id, e.g. scalar types
            $parameterValue = $value->getId()->toBinary();
            $parameterName = uniqid($key);
            $qb->andWhere('c.'.$key.'= :'.$parameterName)
                ->setParameter($parameterName, $parameterValue)
            ;
        }

        $this->updateQueryWithAndContainingBoardOrExpressions($qb, $getFinished);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountOfCasesWithNewHearingPostBy(array $criteria): int
    {
        // TODO: Update beneath when hearing stuff has been implemented
        return -1;
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
        $qb = $this->updateQueryBuilderWithBoardFinishStatuses($qb);

        return $qb;
    }

    public function updateQueryBuilderForBoardMember(BoardMember $boardMember, QueryBuilder $qb): QueryBuilder
    {
        $qb->leftJoin('c.agendaCaseItems', 'aci')
            ->leftJoin('aci.agenda', 'a')
            ->where(':boardMember MEMBER OF a.boardmembers')
            ->setParameter('boardMember', $boardMember->getId()->toBinary())
        ;

        // The status that is considered finished may vary from board to board
        $qb = $this->updateQueryBuilderWithBoardFinishStatuses($qb);

        return $qb;
    }

    public function updateQueryBuilderWithBoardFinishStatuses(QueryBuilder $qb, bool $getFinished = true): QueryBuilder
    {
        $boardRepository = $this->getEntityManager()->getRepository(Board::class);
        $boards = $boardRepository->findAll();

        $count = 0;
        foreach ($boards as $board) {
            $rawPlaces = explode(
                PHP_EOL,
                trim($board->getStatuses())
            );

            $finishedStatus = trim(end($rawPlaces));

            // Construct different variable names for each board
            $statusDQLVariable = 'board_finish_status_'.$count;
            $boardDQLVariable = 'board_'.$count;

            if ($getFinished) {
                $qb->orWhere('c.currentPlace = :'.$statusDQLVariable.' AND c.board = :'.$boardDQLVariable);
            } else {
                $qb->orWhere('c.currentPlace != :'.$statusDQLVariable.' AND c.board = :'.$boardDQLVariable);
            }

            $qb
                ->setParameter($statusDQLVariable, $finishedStatus)
                ->setParameter($boardDQLVariable, $board->getId()->toBinary())
            ;
            ++$count;
        }

        return $qb;
    }

    public function findCountOfCases(array $criteria, bool $getFinished = true): int
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)');

        foreach ($criteria as $key => $value) {
            // TODO: Update beneath to include objects without an id, e.g. scalar types
            $parameterValue = $value->getId()->toBinary();
            $parameterName = uniqid($key);
            $qb->andWhere('c.'.$key.'= :'.$parameterName)
                ->setParameter($parameterName, $parameterValue)
            ;
        }

        $this->updateQueryWithAndContainingBoardOrExpressions($qb, $getFinished);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function updateQueryWithAndContainingBoardOrExpressions(QueryBuilder $qb, bool $getFinished)
    {
        // Expression to collect an or expression per board
        $boardExpression = $qb->expr()->orX();

        $boardRepository = $this->getEntityManager()->getRepository(Board::class);
        $boards = $boardRepository->findAll();

        $count = 0;

        foreach ($boards as $board) {
            $rawPlaces = explode(
                PHP_EOL,
                trim($board->getStatuses())
            );

            $finishedStatus = trim(end($rawPlaces));

            // Construct different variable names for each board
            $statusDQLVariable = 'board_finish_status_'.$count;
            $boardDQLVariable = 'board_'.$count;

            if ($getFinished) {
                $boardExpression->add($qb->expr()->andX(
                    $qb->expr()->eq('c.currentPlace', ':'.$statusDQLVariable),
                    $qb->expr()->eq('c.board', ':'.$boardDQLVariable),
                ));
            } else {
                $boardExpression->add($qb->expr()->andX(
                    $qb->expr()->neq('c.currentPlace', ':'.$statusDQLVariable),
                    $qb->expr()->eq('c.board', ':'.$boardDQLVariable),
                ));
            }

            $qb
                ->setParameter($statusDQLVariable, $finishedStatus)
                ->setParameter($boardDQLVariable, $board->getId()->toBinary())
            ;
            ++$count;
        }

        $qb->andWhere($boardExpression);

//        return $qb;
    }
}
