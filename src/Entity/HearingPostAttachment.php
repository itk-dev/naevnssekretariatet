<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\HearingPostAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=HearingPostAttachmentRepository::class)
 */
class HearingPostAttachment implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\ManyToOne(targetEntity=HearingPost::class, inversedBy="attachments")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private ?\App\Entity\HearingPost $hearingPost = null;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private ?\App\Entity\Document $document = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $position = 0;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHearingPost(): ?HearingPost
    {
        return $this->hearingPost;
    }

    public function setHearingPost(?HearingPost $hearingPost): self
    {
        $this->hearingPost = $hearingPost;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'document',
            'position',
        ];
    }
}
