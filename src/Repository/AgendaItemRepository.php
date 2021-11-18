<?php

namespace App\Repository;

use App\Entity\Agenda;
use App\Entity\AgendaItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaItem[]    findAll()
 * @method AgendaItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaItem::class);
    }

    public function findAscendingAgendaItemsByAgenda(Agenda $agenda): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.agenda = :agenda')
            ->setParameter('agenda', $agenda->getId()->toBinary())
            ->orderBy('a.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
