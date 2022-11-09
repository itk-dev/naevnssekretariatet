<?php

namespace App\Repository;

use App\Entity\AgendaProtocol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaProtocol|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaProtocol|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaProtocol[]    findAll()
 * @method AgendaProtocol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaProtocolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaProtocol::class);
    }
}
