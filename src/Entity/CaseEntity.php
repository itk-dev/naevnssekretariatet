<?php

namespace App\Entity;

use App\Repository\CaseEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=CaseEntityRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"caseEntity" = "CaseEntity", "residentComplaintBoardCase" = "ResidentComplaintBoardCase"})
 * @ORM\EntityListeners({"App\Logging\EntityListener\CaseListener"})
 */
abstract class CaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="caseEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="caseEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseNumber;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=ComplaintCategory::class, inversedBy="caseEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $complaintCategory;

    /**
     * @ORM\OneToMany(targetEntity="CaseDocumentRelation", mappedBy="case")
     */
    private $caseDocumentRelation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $currentPlace;

    /**
     * @ORM\OneToMany(targetEntity="CasePartyRelation", mappedBy="case")
     */
    private $casePartyRelation;

    /**
     * @ORM\OneToMany(targetEntity=Note::class, mappedBy="caseEntity")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity=AgendaCaseItem::class, mappedBy="caseEntity")
     */
    private $agendaCaseItems;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $isReadyForAgenda = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $shouldBeInspected = false;

    public function __construct()
    {
        $this->casePartyRelation = new ArrayCollection();
        $this->caseDocumentRelation = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->agendaCaseItems = new ArrayCollection();
    }

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

        return $this;
    }

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getComplaintCategory(): ?ComplaintCategory
    {
        return $this->complaintCategory;
    }

    public function setComplaintCategory(?ComplaintCategory $complaintCategory): self
    {
        $this->complaintCategory = $complaintCategory;

        return $this;
    }

    /**
     * @return Collection|CaseDocumentRelation[]
     */
    public function getCaseDocumentRelation(): Collection
    {
        return $this->caseDocumentRelation;
    }

    public function addCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        if (!$this->caseDocumentRelation->contains($caseDocumentRelation)) {
            $this->caseDocumentRelation[] = $caseDocumentRelation;
        }

        return $this;
    }

    public function removeCaseDocumentRelation(CaseDocumentRelation $caseDocumentRelation): self
    {
        $this->caseDocumentRelation->removeElement($caseDocumentRelation);

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setCaseEntity($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getCaseEntity() === $this) {
                $note->setCaseEntity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CasePartyRelation[]
     */
    public function getCasePartyRelation(): Collection
    {
        return $this->casePartyRelation;
    }

    public function addCasePartyRelation(CasePartyRelation $casePartyRelation): self
    {
        if (!$this->casePartyRelation->contains($casePartyRelation)) {
            $this->casePartyRelation[] = $casePartyRelation;
        }

        return $this;
    }

    public function removeCasePartyRelation(CasePartyRelation $casePartyRelation): self
    {
        $this->casePartyRelation->removeElement($casePartyRelation);

        return $this;
    }

    public function getCurrentPlace(): ?string
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace(string $currentPlace): self
    {
        $this->currentPlace = $currentPlace;

        return $this;
    }

    public function __toString()
    {
        return $this->caseNumber;
    }

    /**
     * @return Collection|AgendaCaseItem[]
     */
    public function getAgendaCaseItems(): Collection
    {
        return $this->agendaCaseItems;
    }

    public function addAgendaCaseItem(AgendaCaseItem $agendaCaseItem): self
    {
        if (!$this->agendaCaseItems->contains($agendaCaseItem)) {
            $this->agendaCaseItems[] = $agendaCaseItem;
            $agendaCaseItem->setCaseEntity($this);
        }

        return $this;
    }

    public function removeAgendaCaseItem(AgendaCaseItem $agendaCaseItem): self
    {
        if ($this->agendaCaseItems->removeElement($agendaCaseItem)) {
            // set the owning side to null (unless already changed)
            if ($agendaCaseItem->getCaseEntity() === $this) {
                $agendaCaseItem->setCaseEntity(null);
            }
        }

        return $this;
    }

    public function getIsReadyForAgenda(): ?bool
    {
        return $this->isReadyForAgenda;
    }

    public function setIsReadyForAgenda(bool $isReadyForAgenda): self
    {
        $this->isReadyForAgenda = $isReadyForAgenda;

        return $this;
    }

    public function getShouldBeInspected(): ?bool
    {
        return $this->shouldBeInspected;
    }

    public function setShouldBeInspected(bool $shouldBeInspected): self
    {
        $this->shouldBeInspected = $shouldBeInspected;

        return $this;
    }
}
