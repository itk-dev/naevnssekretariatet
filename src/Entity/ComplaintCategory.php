<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\ComplaintCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=ComplaintCategoryRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\ComplaintCategoryListener"})
 */
class ComplaintCategory implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fee;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="complaintCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    /**
     * @ORM\OneToMany(targetEntity=CaseEntity::class, mappedBy="complaintCategory")
     */
    private $caseEntities;

    /**
     * @ORM\ManyToMany(targetEntity=Board::class, inversedBy="complaintCategories")
     */
    private $boards;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->caseEntities = new ArrayCollection();
        $this->boards = new ArrayCollection();
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

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): self
    {
        $this->municipality = $municipality;

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
            $caseEntity->setComplaintCategory($this);
        }

        return $this;
    }

    public function removeCaseEntity(CaseEntity $caseEntity): self
    {
        if ($this->caseEntities->removeElement($caseEntity)) {
            // set the owning side to null (unless already changed)
            if ($caseEntity->getComplaintCategory() === $this) {
                $caseEntity->setComplaintCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getLoggableProperties(): array
    {
        return [
            'name',
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
}
