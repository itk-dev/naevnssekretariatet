<?php

namespace App\Entity;

use App\Repository\AgendaCaseItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\EntityListeners({"App\Logging\EntityListener\AgendaCaseItemListener"})
 *
 * @ORM\Entity(repositoryClass=AgendaCaseItemRepository::class)
 */
class AgendaCaseItem extends AgendaItem
{
    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $inspection = false;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEntity::class, inversedBy="agendaCaseItems")
     */
    private $caseEntity;

    /**
     * @ORM\ManyToMany(targetEntity=Document::class, inversedBy="agendaCaseItems")
     * @ORM\OrderBy({"uploadedAt" = "ASC"})
     */
    private $documents;

    /**
     * @ORM\OneToOne(targetEntity=CasePresentation::class, cascade={"persist", "remove"})
     */
    private $presentation;

    /**
     * @ORM\OneToOne(targetEntity=CaseDecisionProposal::class, cascade={"persist", "remove"})
     */
    private $decisionProposal;

    public function __construct()
    {
        parent::__construct();
        $this->documents = new ArrayCollection();
    }

    public function getInspection(): ?bool
    {
        return $this->inspection;
    }

    public function setInspection(bool $inspection): self
    {
        $this->inspection = $inspection;

        return $this;
    }

    public function getCaseEntity(): ?CaseEntity
    {
        return $this->caseEntity;
    }

    public function setCaseEntity(?CaseEntity $caseEntity): self
    {
        $this->caseEntity = $caseEntity;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        $this->documents->removeElement($document);

        return $this;
    }

    public function getPresentation(): ?CasePresentation
    {
        return $this->presentation;
    }

    public function setPresentation(?CasePresentation $presentation): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getDecisionProposal(): ?CaseDecisionProposal
    {
        return $this->decisionProposal;
    }

    public function setDecisionProposal(?CaseDecisionProposal $decisionProposal): self
    {
        $this->decisionProposal = $decisionProposal;

        return $this;
    }
}
