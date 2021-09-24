<?php

namespace App\Repository;

use App\Entity\AgendaCaseItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaCaseItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaCaseItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaCaseItem[]    findAll()
 * @method AgendaCaseItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaCaseItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaCaseItem::class);
    }
}
