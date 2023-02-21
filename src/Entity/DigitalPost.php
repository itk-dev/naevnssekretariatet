<?php

namespace App\Entity;

use App\Entity\DigitalPost\Recipient;
use App\Repository\DigitalPostRepository;
use App\Service\DigitalPostHelper;
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
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_SENT,
        self::STATUS_ERROR,
        self::STATUS_FAILED,
    ];

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
     * @ORM\OneToMany(targetEntity=Recipient::class, mappedBy="digitalPost", orphanRemoval=true, cascade={"persist"})
     */
    private $recipients;

    /**
     * @ORM\OneToMany(targetEntity=DigitalPostAttachment::class, mappedBy="digitalPost", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $attachments;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    /**
     * @ORM\OneToOne(targetEntity=DigitalPost::class, inversedBy="previous", cascade={"persist", "remove"})
     */
    private $next;

    /**
     * @ORM\OneToOne(targetEntity=DigitalPost::class, mappedBy="next", cascade={"persist", "remove"})
     */
    private $previous;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalFileSize = 0;

    /**
     * @ORM\OneToOne(targetEntity=CaseEvent::class, mappedBy="digitalPost", cascade={"persist", "remove"})
     */
    private $caseEvent;

    /**
     * @ORM\OneToMany(targetEntity=DigitalPostEnvelope::class, mappedBy="digitalPost", orphanRemoval=true, cascade={"persist"})
     */
    private $envelopes;

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

    /**
     * Get subject.
     *
     * @see DigitalPostHelper::SUBJECT_MAX_LENGTH.
     *
     * @param bool $truncate If set, the subject will be truncated to the maximum length allowed when actually sending the post
     */
    public function getSubject(bool $truncate = false): ?string
    {
        return $truncate ? mb_substr($this->subject, 0, DigitalPostHelper::SUBJECT_MAX_LENGTH) : $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getNext(): ?self
    {
        return $this->next;
    }

    public function setNext(?self $next): self
    {
        $this->next = $next;

        return $this;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    public function setPrevious(?self $previous): self
    {
        // unset the owning side of the relation if necessary
        if (null === $previous && null !== $this->previous) {
            $this->previous->setNext(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $previous && $previous->getNext() !== $this) {
            $previous->setNext($this);
        }

        $this->previous = $previous;

        return $this;
    }

    public function getTotalFileSize(): ?int
    {
        return $this->totalFileSize;
    }

    public function setTotalFileSize(int $totalFileSize): self
    {
        $this->totalFileSize = $totalFileSize;

        return $this;
    }

    public function getCaseEvent(): ?CaseEvent
    {
        return $this->caseEvent;
    }

    public function setCaseEvent(?CaseEvent $caseEvent): self
    {
        // unset the owning side of the relation if necessary
        if (null === $caseEvent && null !== $this->caseEvent) {
            $this->caseEvent->setDigitalPost(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $caseEvent && $caseEvent->getDigitalPost() !== $this) {
            $caseEvent->setDigitalPost($this);
        }

        $this->caseEvent = $caseEvent;

        return $this;
    }

    public function getEnvelopes()
    {
        return $this->envelopes;
    }

    public function setEnvelopes($envelopes): self
    {
        $this->envelopes = $envelopes;

        return $this;
    }
}
