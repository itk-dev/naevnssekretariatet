<?php

namespace App\Entity;

use App\Repository\LogEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=LogEntryRepository::class)
 */
class LogEntry
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
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
     * @ORM\Column(type="string", length=1000)
     */
    private $data;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $timeStamp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user;

    public function getId(): ?UuidV4
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

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $timeStamp): self
    {
        $this->timeStamp = $timeStamp;

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
