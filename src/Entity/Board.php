<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BoardRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\BoardListener"})
 */
class Board implements LoggableEntityInterface
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
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="boards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    /**
     * @ORM\OneToMany(targetEntity=SubBoard::class, mappedBy="mainBoard")
     */
    private $subBoards;

    /**
     * @ORM\OneToMany(targetEntity=ComplaintCategory::class, mappedBy="board")
     */
    private $complaintCategories;

    /**
     * @ORM\OneToMany(targetEntity=CaseEntity::class, mappedBy="board")
     */
    private $caseEntities;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseFormType;

    /**
     * @Assert\Positive
     * @ORM\Column(type="integer")
     */
    private $defaultDeadline;

    public function __construct()
    {
        $this->subBoards = new ArrayCollection();
        $this->complaintCategories = new ArrayCollection();
        $this->caseEntities = new ArrayCollection();
    }

    public function getId(): ?UuidV4
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

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection|SubBoard[]
     */
    public function getSubBoards(): Collection
    {
        return $this->subBoards;
    }

    public function addSubBoard(SubBoard $subBoard): self
    {
        if (!$this->subBoards->contains($subBoard)) {
            $this->subBoards[] = $subBoard;
            $subBoard->setMainBoard($this);
        }

        return $this;
    }

    public function removeSubBoard(SubBoard $subBoard): self
    {
        if ($this->subBoards->removeElement($subBoard)) {
            // set the owning side to null (unless already changed)
            if ($subBoard->getMainBoard() === $this) {
                $subBoard->setMainBoard(null);
            }
        }

        return $this;
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
            $complaintCategory->setBoard($this);
        }

        return $this;
    }

    public function removeComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if ($this->complaintCategories->removeElement($complaintCategory)) {
            // set the owning side to null (unless already changed)
            if ($complaintCategory->getBoard() === $this) {
                $complaintCategory->setBoard(null);
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
            $caseEntity->setBoard($this);
        }

        return $this;
    }

    public function removeCaseEntity(CaseEntity $caseEntity): self
    {
        if ($this->caseEntities->removeElement($caseEntity)) {
            // set the owning side to null (unless already changed)
            if ($caseEntity->getBoard() === $this) {
                $caseEntity->setBoard(null);
            }
        }

        return $this;
    }

    public function getCaseFormType(): ?string
    {
        return $this->caseFormType;
    }

    public function setCaseFormType(string $caseFormType): self
    {
        $this->caseFormType = $caseFormType;

        return $this;
    }
  
    public function getDefaultDeadline(): ?int
    {
        return $this->defaultDeadline;
    }

    public function setDefaultDeadline(int $defaultDeadline): self
    {
        $this->defaultDeadline = $defaultDeadline;

        return $this;
    }
  
    public function getLoggableProperties(): array
    {
        return [
            'id',
            'name',
            'caseFormType',
        ];
    }
}
