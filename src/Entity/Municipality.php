<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\MunicipalityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MunicipalityRepository::class)]
#[ORM\EntityListeners([\App\Logging\EntityListener\MunicipalityListener::class])]
class Municipality implements LoggableEntityInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['mail_template'])]
    private ?string $name = null;

    #[ORM\OneToMany(targetEntity: Board::class, mappedBy: 'municipality')]
    private \Doctrine\Common\Collections\ArrayCollection|array $boards;

    #[ORM\OneToMany(targetEntity: ComplaintCategory::class, mappedBy: 'municipality')]
    private \Doctrine\Common\Collections\ArrayCollection|array $complaintCategories;

    #[ORM\OneToMany(targetEntity: CaseEntity::class, mappedBy: 'municipality')]
    private \Doctrine\Common\Collections\ArrayCollection|array $caseEntities;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->boards = new ArrayCollection();
        $this->complaintCategories = new ArrayCollection();
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
            $board->setMunicipality($this);
        }

        return $this;
    }

    public function removeBoard(Board $board): self
    {
        if ($this->boards->removeElement($board)) {
            // set the owning side to null (unless already changed)
            if ($board->getMunicipality() === $this) {
                $board->setMunicipality(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return Collection|ComplaintCategory[]
     */
    public function getComplaintCategories(): Collection
    {
        return $this->complaintCategories;
    }

    public function addComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if (!$this->complaintCategories->contains($complaintCategory)) {
            $this->complaintCategories[] = $complaintCategory;
            $complaintCategory->setMunicipality($this);
        }

        return $this;
    }

    public function removeComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if ($this->complaintCategories->removeElement($complaintCategory)) {
            // set the owning side to null (unless already changed)
            if ($complaintCategory->getMunicipality() === $this) {
                $complaintCategory->setMunicipality(null);
            }
        }

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
            $caseEntity->setMunicipality($this);
        }

        return $this;
    }

    public function removeCaseEntity(CaseEntity $caseEntity): self
    {
        if ($this->caseEntities->removeElement($caseEntity)) {
            // set the owning side to null (unless already changed)
            if ($caseEntity->getMunicipality() === $this) {
                $caseEntity->setMunicipality(null);
            }
        }

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'name',
        ];
    }
}
