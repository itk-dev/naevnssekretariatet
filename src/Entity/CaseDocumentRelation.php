<?php

namespace App\Entity;

use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

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
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="caseDocumentRelation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $case;

    /**
     * @ORM\ManyToOne(targetEntity="Document", inversedBy="caseDocumentRelation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    public function getId(): ?UuidV4
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
}
