<?php

namespace App\Repository;

use App\Entity\HearingPostAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HearingPostAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method HearingPostAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method HearingPostAttachment[]    findAll()
 * @method HearingPostAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HearingPostAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HearingPostAttachment::class);
    }
}
