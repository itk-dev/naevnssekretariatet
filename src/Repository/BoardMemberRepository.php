<?php

namespace App\Repository;

use App\Entity\Agenda;
use App\Entity\BoardMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BoardMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoardMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoardMember[]    findAll()
 * @method BoardMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoardMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardMember::class);
    }

    /**
     * Gets members and their roles by agenda.
     *
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getMembersAndRolesByAgenda(Agenda $agenda): array
    {
        $em = $this->getEntityManager();

        $sql = '
            SELECT m.id,
                   m.name,
                   br.title
            FROM   board_member m
                   JOIN board_role_board_member brbm
                     ON brbm.board_member_id = m.id
                   JOIN board_role br
                     ON brbm.board_role_id = br.id
                   JOIN agenda_board_member abm
                     ON abm.board_member_id = m.id
                   JOIN agenda a
                     ON abm.agenda_id = a.id
            WHERE  a.id = :agenda_id
                   AND br.board_id = :board_id
        ';

        $stmt = $em->getConnection()->prepare($sql);

        return $stmt->executeQuery(
            [
                ':agenda_id' => $agenda->getId()->toBinary(),
                ':board_id' => $agenda->getBoard()->getId()->toBinary(),
            ]
        )->fetchAllAssociative();
    }

    public function getAvailableBoardMembersByAgenda(Agenda $agenda): array
    {
        return $this->createQueryBuilder('bm')
            ->where(':board MEMBER OF bm.boards')
            ->setParameter('board', $agenda->getBoard()->getId()->toBinary())
            ->andWhere(':agenda NOT MEMBER OF bm.agendas')
            ->setParameter('agenda', $agenda->getId()->toBinary())
            ->orderBy('bm.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
