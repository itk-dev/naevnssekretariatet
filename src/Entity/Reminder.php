<?php

namespace App\Entity;

use App\Repository\ReminderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ReminderRepository::class)
 */
class Reminder
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $status = null;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEntity::class, inversedBy="reminders")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Entity\CaseEntity $caseEntity = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reminders")
     */
    private ?\App\Entity\User $createdBy = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
