<?php

namespace App\Entity;

use App\Entity\DigitalPost\Recipient;
use App\Repository\DigitalPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DigitalPostRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(name="entity_idx", columns={"entity_type", "entity_id"})
 * })
 * @ORM\HasLifecycleCallbacks()
 */
class DigitalPost
{
    use TimestampableEntity;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $entityType;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private ?Uuid $entityId;

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

    /**
     * @ORM\OneToMany(targetEntity=DigitalPostAttachment::class, mappedBy="digitalPost", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $attachments;

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

    public function getEntityType(): ?string
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

    public function getEntityId(): ?Uuid
    {
        return $this->entityId;
    }

    /**
     * @return DigitalPost
     */
    public function setEntityId(Uuid $entityId): self
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

    /**
     * @return Collection|DigitalPostAttachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(DigitalPostAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setDigitalPost($this);
        }

        return $this;
    }

    public function removeAttachment(DigitalPostAttachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getDigitalPost() === $this) {
                $attachment->setDigitalPost(null);
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
}
