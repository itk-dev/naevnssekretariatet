<?php

namespace App\Service;

use App\Entity\CaseDocumentRelation;
use App\Entity\CaseEntity;
use App\Entity\Document;
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

    /**
     * Finds suitable cases to copy document to.
     */
    public function findSuitableCases(CaseEntity $case, Document $document): array
    {
        // Collect all cases of same type and within same municipality
        $repository = $this->entityManager->getRepository(get_class($case));
        $potentialCases = $repository->findBy(['municipality' => $case->getMunicipality()], ['caseNumber' => 'ASC']);

        $relations = $document->getCaseDocumentRelations();
        $casesThatContainDocument = [];

        foreach ($relations as $relation) {
            // Ensure that cases which have soft deleted the document is an option for re-upload
            if (!$relation->getSoftDeleted()) {
                array_push($casesThatContainDocument, $relation->getCase());
            }
        }

        return array_diff($potentialCases, $casesThatContainDocument);
    }

    /**
     * Copies document to cases.
     */
    public function handleCopyForm(ArrayCollection $cases, Document $document)
    {
        // Cases have already been filtering via findSuitableCases, so simply create new relations
        foreach ($cases as $caseThatNeedsDoc) {
            $relation = new CaseDocumentRelation();
            $relation->setCase($caseThatNeedsDoc);
            $relation->setDocument($document);
            $this->entityManager->persist($relation);
            $this->entityManager->flush();
        }
    }
}
