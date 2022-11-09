<?php

namespace App\Repository;

use App\Entity\CaseEntity;
use App\Entity\DigitalPostAttachment;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DigitalPostAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalPostAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalPostAttachment[]    findAll()
 * @method DigitalPostAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalPostAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalPostAttachment::class);
    }

    public function findByDocumentAndCase(Document $document, CaseEntity $case): array
    {
        $qb = $this->createQueryBuilder('dga');

        $qb->join('dga.digitalPost', 'dp')
            ->where('dp.entityId = :case_id')
            ->setParameter('case_id', $case->getId()->toBinary())
            ->andWhere('dga.document = :search_document')
            ->setParameter(':search_document', $document->getId()->toBinary())
        ;

        return $qb->getQuery()->getResult();
    }
}
