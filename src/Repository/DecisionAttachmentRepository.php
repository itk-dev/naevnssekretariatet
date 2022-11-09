<?php

namespace App\Repository;

use App\Entity\DecisionAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DecisionAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method DecisionAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method DecisionAttachment[]    findAll()
 * @method DecisionAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DecisionAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DecisionAttachment::class);
    }
}
