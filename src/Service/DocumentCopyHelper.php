<?php

namespace App\Service;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
use App\Repository\CaseDocumentRelationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class DocumentCopyHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findSuitableCases(CaseEntity $case, Document $document): array
    {
        // collect all cases of same type and within same municipality
        $repository = $this->entityManager->getRepository(get_class($case));
        $potentialCases = $repository->findBy(['municipality' => $case->getMunicipality()]);

        $relations = $document->getCaseDocumentRelation();
        $casesThatContainDocument = [];

        foreach ($relations as $relation) {
            // Ensure that cases which have soft deleted the document is an option for re-upload
            if (!$relation->getSoftDeleted()) {
                array_push($casesThatContainDocument, $relation->getCase());
            }
        }

        return array_diff($potentialCases, $casesThatContainDocument);
    }

    public function handleCopyForm(ArrayCollection $cases, Document $document, CaseDocumentRelationRepository $relationRepository)
    {
        foreach ($cases as $caseThatNeedsDoc) {
            // Determine if this case document relation already exists to avoid duplicates
            $existingRelation = $relationRepository->findOneBy(['case' => $caseThatNeedsDoc, 'document' => $document]);

            if (null === $existingRelation) {
                $relation = new CaseDocumentRelation();
                $relation->setCase($caseThatNeedsDoc);
                $relation->setDocument($document);
                $this->entityManager->persist($relation);
            } else {
                $existingRelation->setSoftDeleted(false);
            }
            $this->entityManager->flush();
        }
    }
}
