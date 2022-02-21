<?php

namespace App\Entity;

use App\Repository\DecisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DecisionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Decision
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEntity::class, inversedBy="decisions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $caseEntity;

    /**
     * @ORM\OneToMany(targetEntity=DecisionAttachment::class, mappedBy="decision", orphanRemoval=true)
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $attachments;

    /**
     * @ORM\ManyToMany(targetEntity=Party::class)
     */
    private $recipients;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->attachments = new ArrayCollection();
        $this->recipients = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

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
     * @return Collection|DecisionAttachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(DecisionAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setDecision($this);
        }

        return $this;
    }

    public function removeAttachment(DecisionAttachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getDecision() === $this) {
                $attachment->setDecision(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateAttachmentPositions()
    {
        $index = 0;
        foreach ($this->getAttachments() as $attachment) {
            $attachment->setPosition($index++);
        }
    }

    /**
     * @return Collection|Party[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Party $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
        }

        return $this;
    }

    public function removeRecipient(Party $recipient): self
    {
        $this->recipients->removeElement($recipient);

        return $this;
    }
}
