<?php

namespace App\Repository;

use App\Entity\AgendaCaseItem;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findMany(array $ids): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.id IN (:ids)')
            ->setParameter('ids', array_map(function ($id) {
                return Uuid::fromString($id)->toBinary();
            }, $ids))
            ->getQuery()
            ->getResult();
    }

    public function getAvailableDocumentsForAgendaItem(AgendaCaseItem $agendaCaseItem): array
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->join('d.caseDocumentRelation', 'r')
            ->where('r.softDeleted = false')
            ->andWhere('r.case = :caseId')
            ->setParameter('caseId', $agendaCaseItem->getCaseEntity()->getId(), 'uuid')
        ;

        if (!$agendaCaseItem->getDocuments()->isEmpty()) {
            $qb->andWhere('d.id NOT IN (:agenda_doc_ids)')
                ->setParameter(':agenda_doc_ids', $agendaCaseItem->getDocuments()->map(function (Document $doc) {
                    return $doc->getId()->toBinary();
                }))
            ;
        }

        return $qb
            ->getQuery()
            ->getResult();
    }
}
