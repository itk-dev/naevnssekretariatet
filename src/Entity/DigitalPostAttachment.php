<?php

namespace App\Entity;

use App\Repository\DigitalPostAttachmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DigitalPostAttachmentRepository::class)
 */
class DigitalPostAttachment
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DigitalPost::class, inversedBy="attachments")
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $digitalPost;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     *
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @Gedmo\SortablePosition()
     *
     * @ORM\Column(type="integer")
     */
    private $position;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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
}
