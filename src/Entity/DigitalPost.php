<?php

namespace App\Entity;

use App\Entity\DigitalPost\Recipient;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DigitalPostRepository::class)
 */
class DigitalPost
{
    public const STATUS_SENT = 'sent';
    public const STATUS_ERROR = 'error';

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
     * @ORM\ManyToMany(targetEntity=Document::class)
     */
    private $attachments;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private string $entityType;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private string $entityId;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $sentAt;

    /**
     * @ORM\OneToMany(targetEntity=Recipient::class, mappedBy="digitalPost", orphanRemoval=true)
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

    /**
     * @return Collection|Document[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Document $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
        }

        return $this;
    }

    public function removeAttachment(Document $attachment): self
    {
        $this->attachments->removeElement($attachment);

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return DigitalPost
     */
    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return DigitalPost
     */
    public function setEntityId(string $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addData(array $data): self
    {
        return $this->setData(array_merge($this->getData(), $data));
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return Collection|Recipient[]
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setDigitalPost($this);
        }

        return $this;
    }

    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->removeElement($recipient)) {
            // set the owning side to null (unless already changed)
            if ($recipient->getDigitalPost() === $this) {
                $recipient->setDigitalPost(null);
            }
        }

        return $this;
    }
}