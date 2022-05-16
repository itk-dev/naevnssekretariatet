<?php

namespace App\Entity;

use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=CaseDocumentRelationRepository::class)
 * @ORM\Table(name="case_documents")
 * @ORM\EntityListeners({"App\Logging\EntityListener\CaseDocumentRelationListener"})
 */
class CaseDocumentRelation
{
    use SoftDeletableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="caseDocumentRelation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $case;

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="caseDocumentRelations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $removalReason;

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
