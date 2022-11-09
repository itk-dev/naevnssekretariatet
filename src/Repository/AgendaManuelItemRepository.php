<?php

namespace App\Repository;

use App\Entity\AgendaManuelItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaManuelItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaManuelItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaManuelItem[]    findAll()
 * @method AgendaManuelItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaManuelItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaManuelItem::class);
    }
}
