<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\ComplaintCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ComplaintCategoryRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\ComplaintCategoryListener"})
 */
class ComplaintCategory implements LoggableEntityInterface, \Stringable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?float $fee = null;

    /**
     * @ORM\ManyToMany(targetEntity=Board::class, inversedBy="complaintCategories")
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $boards;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"mail_template"})
     */
    private ?string $kle = null;

    /**
     * @ORM\ManyToMany(targetEntity=CaseEntity::class, mappedBy="complaintCategories")
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $caseEntities;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->boards = new ArrayCollection();
        $this->caseEntities = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(?float $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getLoggableProperties(): array
    {
        return [
            'name',
            'fee',
            'kle',
        ];
    }

    /**
     * @return Collection|Board[]
     */
    public function getBoards(): Collection
    {
        return $this->boards;
    }

    public function addBoard(Board $board): self
    {
        if (!$this->boards->contains($board)) {
            $this->boards[] = $board;
        }

        return $this;
    }

    public function removeBoard(Board $board): self
    {
        $this->boards->removeElement($board);

        return $this;
    }

    public function getKle(): ?string
    {
        return $this->kle;
    }

    public function setKle(?string $kle): self
    {
        $this->kle = $kle;

        return $this;
    }

    /**
     * @return Collection|CaseEntity[]
     */
    public function getCaseEntities(): Collection
    {
        return $this->caseEntities;
    }

    public function addCaseEntity(CaseEntity $caseEntity): self
    {
        if (!$this->caseEntities->contains($caseEntity)) {
            $this->caseEntities[] = $caseEntity;
            $caseEntity->addComplaintCategory($this);
        }

        return $this;
    }

    public function removeCaseEntity(CaseEntity $caseEntity): self
    {
        if ($this->caseEntities->removeElement($caseEntity)) {
            $caseEntity->removeComplaintCategory($this);
        }

        return $this;
    }
}
