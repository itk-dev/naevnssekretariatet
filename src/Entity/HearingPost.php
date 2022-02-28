<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\HearingPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HearingPostRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"hearingPost" = "HearingPost", "hearingPostRequest" = "HearingPostRequest", "hearingPostResponse" = "HearingPostResponse"})
 * @ORM\EntityListeners({"App\Logging\EntityListener\HearingPostListener"})
 * @ORM\HasLifecycleCallbacks()
 */
abstract class HearingPost implements LoggableEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Hearing::class, inversedBy="hearingPosts")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private $hearing;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private $recipient;

    /**
     * @ORM\OneToMany(targetEntity=HearingPostAttachment::class, mappedBy="hearingPost", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"position": "ASC"})
     * @Assert\Valid()
     */
    private $attachments;

    /**
     * @ORM\ManyToOne(targetEntity=MailTemplate::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $forwardedOn;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     */
    private $document;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
     */
    private $title;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->attachments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return self::class.'#'.$this->getId();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getRecipient(): ?Party
    {
        return $this->recipient;
    }

    public function setRecipient(Party $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return Collection|HearingPostAttachment[]
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(HearingPostAttachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments[] = $attachment;
            $attachment->setHearingPost($this);
            // Mark hearing as updated.
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function removeAttachment(HearingPostAttachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getHearingPost() === $this) {
                $attachment->setHearingPost(null);
            }
            // Mark hearing as updated.
            $this->updatedAt = new \DateTime();
        }

        return $this;
    }

    public function getTemplate(): ?MailTemplate
    {
        return $this->template;
    }

    public function setTemplate(?MailTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getForwardedOn(): ?\DateTimeInterface
    {
        return $this->forwardedOn;
    }

    public function setForwardedOn(?\DateTimeInterface $forwardedOn): self
    {
        $this->forwardedOn = $forwardedOn;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'title',
            'recipient',
            'template',
            'forwardedOn',
        ];
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

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
