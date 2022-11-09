<?php

namespace App\Repository;

use App\Entity\CaseDecisionProposal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CaseDecisionProposal|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaseDecisionProposal|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaseDecisionProposal[]    findAll()
 * @method CaseDecisionProposal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaseDecisionProposalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaseDecisionProposal::class);
    }
}
