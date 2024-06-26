<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Repository\CaseEntityRepository;
use App\Traits\BlameableEntity;
use App\Traits\SoftDeletableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CaseEntityRepository::class)
 *
 * @ORM\InheritanceType("JOINED")
 *
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 *
 * @ORM\DiscriminatorMap({"caseEntity" = "CaseEntity", "residentComplaintBoardCase" = "ResidentComplaintBoardCase", "rentBoardCase" = "RentBoardCase", "fenceReviewCase" = "FenceReviewCase"})
 *
 * @ORM\EntityListeners({"App\Logging\EntityListener\CaseListener"})
 */
abstract class CaseEntity implements Timestampable
{
    use BlameableEntity;
    use SoftDeletableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="caseEntities")
     *
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"mail_template"})
     */
    private $board;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="caseEntities")
     *
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"mail_template"})
     */
    private $municipality;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"mail_template"})
     */
    private $caseNumber;

    /**
     * @ORM\OneToMany(targetEntity="CaseDocumentRelation", mappedBy="case")
     */
    private $caseDocumentRelation;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"mail_template"})
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

    /**
     * @ORM\OneToOne(targetEntity=CasePresentation::class, inversedBy="caseEntity", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $presentation;

    /**
     * @ORM\OneToOne(targetEntity=CaseDecisionProposal::class, inversedBy="caseEntity", cascade={"persist", "remove"})
     */
    private $decisionProposal;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedCases")
     *
     * @Groups({"mail_template"})
     */
    private $assignedTo;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"mail_template"})
     */
    private $bringer;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     *
     * @Groups({"mail_template"})
     */
    private $bringerAddress;

    /**
     * @ORM\OneToMany(targetEntity=Reminder::class, mappedBy="caseEntity")
     */
    private $reminders;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sortingAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sortingParty;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sortingCounterparty;

    /**
     * @Assert\GreaterThanOrEqual(propertyPath="finishHearingDeadline", groups={"process_finish"})
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="date")
     */
    private $finishProcessingDeadline;

    /**
     * @Assert\GreaterThanOrEqual("today", groups={"hearing_finish"})
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(type="date")
     */
    private $finishHearingDeadline;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasReachedHearingDeadline = false;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasReachedProcessingDeadline = false;

    /**
     * @ORM\OneToOne(targetEntity=Hearing::class, inversedBy="caseEntity", cascade={"persist", "remove"})
     */
    private $hearing;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Identification")
     *
     * @Groups({"mail_template"})
     *
     * @Assert\Valid()
     */
    private $bringerIdentification;

    /**
     * @ORM\OneToMany(targetEntity=Decision::class, mappedBy="caseEntity", orphanRemoval=true)
     */
    private $decisions;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $removalReason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"mail_template"})
     */
    private $extraComplaintCategoryInformation;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $bringerIsUnderAddressProtection = false;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"mail_template"})
     */
    private $receivedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"mail_template"})
     */
    private $validatedAt;

    /**
     * @Assert\GreaterThanOrEqual("today", groups={"hearing_response_deadline"})
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $hearingResponseDeadline;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hasReachedHearingResponseDeadline = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     *
     * @Groups({"mail_template"})
     */
    private $dateForActiveAgenda;

    /**
     * @ORM\ManyToMany(targetEntity=ComplaintCategory::class, inversedBy="caseEntities")
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "You have to select at least 1 item"
     * )
     */
    private $complaintCategories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $onBehalfOf;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finishedOn;

    /**
     * @ORM\ManyToMany(targetEntity=CaseEvent::class, mappedBy="caseEntities")
     */
    private $caseEvents;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->bringerAddress = new Address();
        $this->casePartyRelation = new ArrayCollection();
        $this->caseDocumentRelation = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->agendaCaseItems = new ArrayCollection();
        $this->reminders = new ArrayCollection();
        $this->finishHearingDeadline = new \DateTime('today');
        $this->finishProcessingDeadline = new \DateTime('today');
        $this->decisions = new ArrayCollection();
        $this->bringerIdentification = new Identification();
        $this->complaintCategories = new ArrayCollection();
        $this->caseEvents = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

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

    public function getBringer(): ?string
    {
        return $this->bringer;
    }

    public function setBringer(?string $bringer): self
    {
        $this->bringer = $bringer;

        return $this;
    }

    public function setBringerAddress(Address $address): void
    {
        $this->bringerAddress = $address;
    }

    public function getBringerAddress(): Address
    {
        return $this->bringerAddress;
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

    public function getSortingAddress(): ?string
    {
        return $this->sortingAddress;
    }

    public function setSortingAddress(string $sortingAddress): self
    {
        $this->sortingAddress = $sortingAddress;

        return $this;
    }

    public function getFinishProcessingDeadline(): ?\DateTimeInterface
    {
        return $this->finishProcessingDeadline;
    }

    public function setFinishProcessingDeadline(?\DateTimeInterface $finishProcessingDeadline): self
    {
        $this->finishProcessingDeadline = $finishProcessingDeadline;

        return $this;
    }

    public function getSortingParty(): ?string
    {
        return $this->sortingParty;
    }

    public function setSortingParty(string $sortingParty): self
    {
        $this->sortingParty = $sortingParty;

        return $this;
    }

    public function getSortingCounterparty(): ?string
    {
        return $this->sortingCounterparty;
    }

    public function setSortingCounterparty(string $sortingCounterparty): self
    {
        $this->sortingCounterparty = $sortingCounterparty;

        return $this;
    }

    public function getFinishHearingDeadline(): ?\DateTimeInterface
    {
        return $this->finishHearingDeadline;
    }

    public function setFinishHearingDeadline(\DateTimeInterface $finishHearingDeadline): self
    {
        $this->finishHearingDeadline = $finishHearingDeadline;

        return $this;
    }

    public function getHasReachedHearingDeadline(): ?bool
    {
        return $this->hasReachedHearingDeadline;
    }

    public function setHasReachedHearingDeadline(bool $hasReachedHearingDeadline): self
    {
        $this->hasReachedHearingDeadline = $hasReachedHearingDeadline;

        return $this;
    }

    public function getHasReachedProcessingDeadline(): ?bool
    {
        return $this->hasReachedProcessingDeadline;
    }

    public function setHasReachedProcessingDeadline(bool $hasReachedProcessingDeadline): self
    {
        $this->hasReachedProcessingDeadline = $hasReachedProcessingDeadline;

        return $this;
    }

    public function getHearing(): ?Hearing
    {
        return $this->hearing;
    }

    public function setHearing(?Hearing $hearing): self
    {
        $this->hearing = $hearing;

        return $this;
    }

    public function getBringerIdentification(): Identification
    {
        return $this->bringerIdentification;
    }

    public function setBringerIdentification(Identification $bringerIdentification): void
    {
        $this->bringerIdentification = $bringerIdentification;
    }

    /**
     * @return Collection|Decision[]
     */
    public function getDecisions(): Collection
    {
        return $this->decisions;
    }

    public function addDecision(Decision $decision): self
    {
        if (!$this->decisions->contains($decision)) {
            $this->decisions[] = $decision;
            $decision->setCaseEntity($this);
        }

        return $this;
    }

    public function removeDecision(Decision $decision): self
    {
        if ($this->decisions->removeElement($decision)) {
            // set the owning side to null (unless already changed)
            if ($decision->getCaseEntity() === $this) {
                $decision->setCaseEntity(null);
            }
        }

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

    /**
     * Returns array indicating identification properties and the properties that upon change should result in invalidation of Identification.
     *
     * Example:
     * [
     * 'bringerIdentification' => [
     *      // Depends on values of
     *      'bringer'
     *      'bringerAddress'
     *      ...
     *  ],
     *  'accusedIdentification' => [
     *      // Depends on values of
     *      'accused'
     *      'accusedAddress'
     *      ...
     *  ],
     * ]
     */
    abstract public function getIdentificationInvalidationProperties(): array;

    public function getExtraComplaintCategoryInformation(): ?string
    {
        return $this->extraComplaintCategoryInformation;
    }

    public function setExtraComplaintCategoryInformation(?string $extraComplaintCategoryInformation): self
    {
        $this->extraComplaintCategoryInformation = $extraComplaintCategoryInformation;

        return $this;
    }

    public function getBringerIsUnderAddressProtection(): ?bool
    {
        return $this->bringerIsUnderAddressProtection;
    }

    public function setBringerIsUnderAddressProtection(bool $bringerIsUnderAddressProtection): self
    {
        $this->bringerIsUnderAddressProtection = $bringerIsUnderAddressProtection;

        return $this;
    }

    public function getReceivedAt(): \DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeInterface $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getHearingResponseDeadline(): ?\DateTimeInterface
    {
        return $this->hearingResponseDeadline;
    }

    public function setHearingResponseDeadline(?\DateTimeInterface $hearingResponseDeadline): self
    {
        $this->hearingResponseDeadline = $hearingResponseDeadline;

        return $this;
    }

    public function getHasReachedHearingResponseDeadline(): ?bool
    {
        return $this->hasReachedHearingResponseDeadline;
    }

    public function setHasReachedHearingResponseDeadline(bool $hasReachedHearingResponseDeadline): self
    {
        $this->hasReachedHearingResponseDeadline = $hasReachedHearingResponseDeadline;

        return $this;
    }

    public function getDateForActiveAgenda(): ?\DateTimeInterface
    {
        return $this->dateForActiveAgenda;
    }

    public function setDateForActiveAgenda(?\DateTimeInterface $dateForActiveAgenda): self
    {
        $this->dateForActiveAgenda = $dateForActiveAgenda;

        return $this;
    }

    /**
     * @return Collection|ComplaintCategory[]
     */
    public function getComplaintCategories(): Collection
    {
        return $this->complaintCategories;
    }

    public function addComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if (!$this->complaintCategories->contains($complaintCategory)) {
            $this->complaintCategories[] = $complaintCategory;
        }

        return $this;
    }

    public function removeComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        $this->complaintCategories->removeElement($complaintCategory);

        return $this;
    }

    public function getOnBehalfOf(): ?string
    {
        return $this->onBehalfOf;
    }

    public function setOnBehalfOf(?string $onBehalfOf): self
    {
        $this->onBehalfOf = $onBehalfOf;

        return $this;
    }

    public function getFinishedOn(): ?\DateTimeInterface
    {
        return $this->finishedOn;
    }

    public function setFinishedOn(?\DateTimeInterface $finishedOn): self
    {
        $this->finishedOn = $finishedOn;

        return $this;
    }

    /**
     * @return Collection|CaseEvent[]
     */
    public function getCaseEvents(): Collection
    {
        return $this->caseEvents;
    }

    public function addCaseEvent(CaseEvent $caseEvent): self
    {
        if (!$this->caseEvents->contains($caseEvent)) {
            $this->caseEvents[] = $caseEvent;
            $caseEvent->setCaseEntity($this);
        }

        return $this;
    }

    public function removeCaseEvent(CaseEvent $caseEvent): self
    {
        if ($this->caseEvents->removeElement($caseEvent)) {
            // set the owning side to null (unless already changed)
            if ($caseEvent->getCaseEntity() === $this) {
                $caseEvent->setCaseEntity(null);
            }
        }

        return $this;
    }
}
