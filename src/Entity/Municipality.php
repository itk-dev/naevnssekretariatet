<?php

namespace App\Entity;

use App\Repository\MunicipalityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=MunicipalityRepository::class)
 */
class Municipality
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
     * @ORM\OneToMany(targetEntity=Board::class, mappedBy="municipality")
     */
    private $boards;

    /**
     * @ORM\OneToMany(targetEntity=SubBoard::class, mappedBy="municipality")
     */
    private $subBoards;

    /**
     * @ORM\OneToMany(targetEntity=Party::class, mappedBy="municipality")
     */
    private $parties;

    /**
     * @ORM\OneToMany(targetEntity=BoardMember::class, mappedBy="municipality")
     */
    private $boardMembers;

    /**
     * @ORM\OneToMany(targetEntity=ComplaintCategory::class, mappedBy="municipality")
     */
    private $complaintCategories;

    public function __construct()
    {
        $this->boards = new ArrayCollection();
        $this->subBoards = new ArrayCollection();
        $this->parties = new ArrayCollection();
        $this->boardMembers = new ArrayCollection();
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
            $subBoard->setMunicipality($this);
        }

        return $this;
    }

    public function removeSubBoard(SubBoard $subBoard): self
    {
        if ($this->subBoards->removeElement($subBoard)) {
            // set the owning side to null (unless already changed)
            if ($subBoard->getMunicipality() === $this) {
                $subBoard->setMunicipality(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Party[]
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(Party $party): self
    {
        if (!$this->parties->contains($party)) {
            $this->parties[] = $party;
            $party->setMunicipality($this);
        }

        return $this;
    }

    public function removeParty(Party $party): self
    {
        if ($this->parties->removeElement($party)) {
            // set the owning side to null (unless already changed)
            if ($party->getMunicipality() === $this) {
                $party->setMunicipality(null);
            }
        }

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
            $boardMember->setMunicipality($this);
        }

        return $this;
    }

    public function removeBoardMember(BoardMember $boardMember): self
    {
        if ($this->boardMembers->removeElement($boardMember)) {
            // set the owning side to null (unless already changed)
            if ($boardMember->getMunicipality() === $this) {
                $boardMember->setMunicipality(null);
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
}
