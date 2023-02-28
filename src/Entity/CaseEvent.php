<?php

namespace App\Entity;

use App\Repository\CaseEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=CaseEventRepository::class)
 */
class CaseEvent
{
    use TimestampableEntity;

    public const CATEGORY_INCOMING = 'Indgående';
    public const CATEGORY_OUTGOING = 'Udgående';
    public const CATEGORY_NOTE = 'Notat';
    public const SUBJECT_CASE_BRINGING = 'Indbringelse';
    public const SUBJECT_HEARING_CONTRADICTIONS_BRIEFING = 'Partshøring, kontradiktioner og orienteringer';

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEntity::class, inversedBy="caseEvents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $caseEntity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\OneToOne(targetEntity=DigitalPost::class, inversedBy="caseEvent", cascade={"persist", "remove"})
     */
    private $digitalPost;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $createdBy;

    /**
     * @ORM\ManyToMany(targetEntity=Document::class)
     */
    private $documents;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $noteContent;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receivedAt;

    /**
     * @ORM\OneToMany(targetEntity=CaseEventPartyRelation::class, mappedBy="caseEvent", orphanRemoval=true)
     */
    private $caseEventPartyRelations;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->documents = new ArrayCollection();
        $this->caseEventPartyRelations = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCaseEntity(): CaseEntity
    {
        return $this->caseEntity;
    }

    public function setCaseEntity(CaseEntity $caseEntity): self
    {
        $this->caseEntity = $caseEntity;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getDigitalPost(): ?DigitalPost
    {
        return $this->digitalPost;
    }

    public function setDigitalPost(?DigitalPost $digitalPost): self
    {
        $this->digitalPost = $digitalPost;

        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(UserInterface $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getNoteContent(): ?string
    {
        return $this->noteContent;
    }

    public function setNoteContent(?string $noteContent): self
    {
        $this->noteContent = $noteContent;

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

    /**
     * @return Collection|CaseEventPartyRelation[]
     */
    public function getCaseEventPartyRelations(): Collection
    {
        return $this->caseEventPartyRelations;
    }

    public function addCaseEventPartyRelation(CaseEventPartyRelation $caseEventPartyRelation): self
    {
        if (!$this->caseEventPartyRelations->contains($caseEventPartyRelation)) {
            $this->caseEventPartyRelations[] = $caseEventPartyRelation;
            $caseEventPartyRelation->setCaseEvent($this);
        }

        return $this;
    }

    public function removeCaseEventPartyRelation(CaseEventPartyRelation $caseEventPartyRelation): self
    {
        if ($this->caseEventPartyRelations->removeElement($caseEventPartyRelation)) {
            // set the owning side to null (unless already changed)
            if ($caseEventPartyRelation->getCaseEvent() === $this) {
                $caseEventPartyRelation->setCaseEvent(null);
            }
        }

        return $this;
    }
}
