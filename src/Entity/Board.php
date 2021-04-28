<?php

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=BoardRepository::class)
 */
class Board
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
     * @ORM\OneToMany(targetEntity=BoardMember::class, mappedBy="board")
     */
    private $boardMembers;

    /**
     * @ORM\OneToMany(targetEntity=SubBoard::class, mappedBy="mainBoard")
     */
    private $subBoards;

    /**
     * @ORM\OneToMany(targetEntity=ComplaintCategory::class, mappedBy="board")
     */
    private $complaintCategories;

    public function __construct()
    {
        $this->boardMembers = new ArrayCollection();
        $this->subBoards = new ArrayCollection();
        $this->complaintCategories = new ArrayCollection();
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

    /**
     * @return Collection|BoardMember[]
     */
    public function getBoardMembers(): Collection
    {
        return $this->boardMembers;
    }

    public function addBoardMember(BoardMember $boardMember): self
    {
        if (!$this->boardMembers->contains($boardMember)) {
            $this->boardMembers[] = $boardMember;
            $boardMember->setBoard($this);
        }

        return $this;
    }

    public function removeBoardMember(BoardMember $boardMember): self
    {
        if ($this->boardMembers->removeElement($boardMember)) {
            // set the owning side to null (unless already changed)
            if ($boardMember->getBoard() === $this) {
                $boardMember->setBoard(null);
            }
        }

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
}
