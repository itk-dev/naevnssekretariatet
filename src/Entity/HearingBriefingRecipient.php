<?php

namespace App\Entity;

use App\Repository\HearingBriefingRecipientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=HearingBriefingRecipientRepository::class)
 */
class HearingBriefingRecipient
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=HearingBriefing::class, inversedBy="hearingBriefingRecipients")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $hearingBriefing;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class)
     *
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"mail_template"})
     */
    private $recipient;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\ManyToMany(targetEntity=Document::class)
     */
    private $attachments;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->attachments = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHearingBriefing(): ?HearingBriefing
    {
        return $this->hearingBriefing;
    }

    public function setHearingBriefing(?HearingBriefing $hearingBriefing): self
    {
        $this->hearingBriefing = $hearingBriefing;

        return $this;
    }

    public function getRecipient(): ?Party
    {
        return $this->recipient;
    }

    public function setRecipient(?Party $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
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

    public function __toString(): string
    {
        return $this->getRecipient()->getName();
    }

    /**
     * @return Collection<int, Document>
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
}
