<?php

namespace App\Repository;

use App\Entity\MailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MailTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method MailTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method MailTemplate[]    findAll()
 * @method MailTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MailTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MailTemplate::class);
    }
}
