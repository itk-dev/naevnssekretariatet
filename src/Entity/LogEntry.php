<?php

namespace App\Entity;

use App\Repository\LogEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass=LogEntryRepository::class)
 */
class LogEntry
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="ulid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entityType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entityID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $action;

    /**
     * @ORM\Column(type="json")
     */
    private $data;

    /**
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user;

    public function __construct()
    {
        $this->id = new Ulid();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getCaseID(): ?string
    {
        return $this->caseID;
    }

    public function setCaseID(string $caseID): self
    {
        $this->caseID = $caseID;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntity(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityID(): ?string
    {
        return $this->entityID;
    }

    public function setEntityID(string $entityID): self
    {
        $this->entityID = $entityID;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }
}
