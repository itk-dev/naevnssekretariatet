<?php

namespace App\Repository;

use App\Entity\Agenda;
use App\Entity\BoardMember;
use App\Entity\CaseEntity;
use App\Service\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\UuidV4;

/**
 * @method Agenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agenda[]    findAll()
 * @method Agenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getFinishedAgendaDataByCase(CaseEntity $case): array
    {
        $em = $this->getEntityManager();

        $sql = '
            SELECT a.date AS agenda_date, a.start AS agenda_start, a.end AS agenda_end, a.status AS agenda_status, a.id AS agenda_id, b.name AS board_name, c.should_be_inspected AS case_should_be_inspected
            FROM agenda AS a
                JOIN board AS b
                  ON a.board_id = b.id
                JOIN agenda_item AS ai
                  ON a.id = ai.agenda_id
                JOIN agenda_case_item AS aci
                  ON aci.id = ai.id
                JOIN case_entity AS c
                  ON aci.case_entity_id = c.id
            WHERE c.id = :case_id
              AND a.status = :agenda_status;
        ';

        $stmt = $em->getConnection()->prepare($sql);

        $result = $stmt->executeQuery(
            [
                ':case_id' => $case->getId()->toBinary(),
                ':agenda_status' => AgendaStatus::FINISHED,
            ]
        )->fetchAllAssociative();

        foreach ($result as &$agenda) {
            $agenda['agenda_id'] = UuidV4::fromString($agenda['agenda_id'])->__toString();
        }

        return $result;
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getActiveAgendaDataByCase(CaseEntity $case): array
    {
        $em = $this->getEntityManager();

        $sql = '
            SELECT a.date AS agenda_date, a.start AS agenda_start, a.end AS agenda_end, a.status AS agenda_status, a.id AS agenda_id, b.name AS board_name, c.should_be_inspected AS case_should_be_inspected
            FROM agenda AS a
                JOIN board AS b
                  ON a.board_id = b.id
                JOIN agenda_item AS ai
                  ON a.id = ai.agenda_id
                JOIN agenda_case_item AS aci
                  ON aci.id = ai.id
                JOIN case_entity AS c
                  ON aci.case_entity_id = c.id
            WHERE c.id = :case_id
              AND a.status != :agenda_status;
        ';

        $stmt = $em->getConnection()->prepare($sql);

        $result = $stmt->executeQuery(
            [
                ':case_id' => $case->getId()->toBinary(),
                ':agenda_status' => AgendaStatus::FINISHED,
            ]
        )->fetchAllAssociative();

        foreach ($result as &$agenda) {
            $agenda['agenda_id'] = UuidV4::fromString($agenda['agenda_id'])->__toString();
        }

        return $result;
    }

    public function createQueryBuilderForBoardMember(BoardMember $boardMember)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->where(':boardMember MEMBER OF a.boardmembers')
            ->setParameter('boardMember', $boardMember->getId()->toBinary())
            ->orWhere('a.status = :agenda_finished_status')
            ->setParameter('agenda_finished_status', AgendaStatus::FINISHED)
        ;

        return $qb;
    }
}
