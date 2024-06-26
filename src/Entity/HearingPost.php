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
 *
 * @ORM\InheritanceType("JOINED")
 *
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 *
 * @ORM\DiscriminatorMap({"hearingPost" = "HearingPost", "hearingPostRequest" = "HearingPostRequest", "hearingPostResponse" = "HearingPostResponse"})
 *
 * @ORM\EntityListeners({"App\Logging\EntityListener\HearingPostListener"})
 *
 * @ORM\HasLifecycleCallbacks()
 */
abstract class HearingPost implements LoggableEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Hearing::class, inversedBy="hearingPosts")
     *
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups({"mail_template"})
     */
    private $hearing;

    /**
     * @ORM\OneToMany(targetEntity=HearingPostAttachment::class, mappedBy="hearingPost", orphanRemoval=true, cascade={"persist"})
     *
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid()
     */
    private $attachments;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->attachments = new ArrayCollection();
    }

    public function __toString(): string
    {
        $case = $this->hearing->getCaseEntity();

        return sprintf('Hearing post (case: %s)', $case->getCaseNumber());
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

    /**
     * @ORM\PrePersist
     *
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
