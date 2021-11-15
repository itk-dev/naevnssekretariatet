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
 * @ORM\DiscriminatorMap({"caseEntity" = "CaseEntity", "residentComplaintBoardCase" = "ResidentComplaintBoardCase", "rentBoardCase" = "RentBoardCase", "fenceReviewCase" = "FenceReviewCase"})
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
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedCases")
     */
    private $assignedTo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainant;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantStreetNameAndNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantZip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantCity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantCPR;

    /**
     * @ORM\OneToMany(targetEntity=Reminder::class, mappedBy="caseEntity")
     */
    private $reminders;
  
    public function __construct()
    {
        $this->casePartyRelation = new ArrayCollection();
        $this->caseDocumentRelation = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->reminders = new ArrayCollection();
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

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getComplainant(): ?string
    {
        return $this->complainant;
    }

    public function setComplainant(?string $complainant): self
    {
        $this->complainant = $complainant;

        return $this;
    }

    public function getComplainantStreetNameAndNumber(): ?string
    {
        return $this->complainantStreetNameAndNumber;
    }

    public function setComplainantStreetNameAndNumber(?string $complainantStreetNameAndNumber): self
    {
        $this->complainantStreetNameAndNumber = $complainantStreetNameAndNumber;

        return $this;
    }

    public function getComplainantZip(): ?string
    {
        return $this->complainantZip;
    }

    public function setComplainantZip(?string $complainantZip): self
    {
        $this->complainantZip = $complainantZip;

        return $this;
    }

    public function getComplainantCity(): ?string
    {
        return $this->complainantCity;
    }

    public function setComplainantCity(?string $complainantCity): self
    {
        $this->complainantCity = $complainantCity;

        return $this;
    }

    public function getComplainantCPR(): ?string
    {
        return $this->complainantCPR;
    }

    public function setComplainantCPR(string $complainantCPR): self
    {
        $this->complainantCPR = $complainantCPR;

        return $this;
    }

    public function __toString()
    {
        return $this->caseNumber;
    }

    /**
     * @return Collection|Reminder[]
     */
    public function getReminders(): Collection
    {
        return $this->reminders;
    }

    public function addReminder(Reminder $reminder): self
    {
        if (!$this->reminders->contains($reminder)) {
            $this->reminders[] = $reminder;
            $reminder->setCaseEntity($this);
        }

        return $this;
    }

    public function removeReminder(Reminder $reminder): self
    {
        if ($this->reminders->removeElement($reminder)) {
            // set the owning side to null (unless already changed)
            if ($reminder->getCaseEntity() === $this) {
                $reminder->setCaseEntity(null);
            }
        }

        return $this;
    }
}
