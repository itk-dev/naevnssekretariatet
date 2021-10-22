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
            select m.id, m.name, br.title
            from board_member m
            join board_role_board_member brbm on brbm.board_member_id = m.id
            join board_role br on brbm.board_role_id = br.id
            join agenda_board_member abm on abm.board_member_id = m.id
            join agenda a on abm.agenda_id = a.id
            where a.id = :agenda_id
        ';

        $stmt = $em->getConnection()->prepare($sql);

        return $stmt->executeQuery([':agenda_id' => $agenda->getId()->toBinary()])->fetchAllAssociative();
    }
}
