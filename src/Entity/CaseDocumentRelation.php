<?php

namespace App\Entity;

use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'case_documents')]
#[ORM\Entity(repositoryClass: CaseDocumentRelationRepository::class)]
#[ORM\EntityListeners([\App\Logging\EntityListener\CaseDocumentRelationListener::class])]
class CaseDocumentRelation
{
    use SoftDeletableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\ManyToOne(targetEntity: 'CaseEntity', inversedBy: 'caseDocumentRelation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\CaseEntity $case = null;

    #[ORM\ManyToOne(targetEntity: 'Document', inversedBy: 'caseDocumentRelations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\Document $document = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $removalReason = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCase(): ?CaseEntity
    {
        return $this->case;
    }

    public function setCase(CaseEntity $case): self
    {
        $this->case = $case;

        return $this;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getRemovalReason(): ?string
    {
        return $this->removalReason;
    }

    public function setRemovalReason(?string $removalReason): self
    {
        $this->removalReason = $removalReason;

        return $this;
    }
}
