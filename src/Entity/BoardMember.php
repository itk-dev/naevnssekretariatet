<?php

namespace App\Entity;

use App\Repository\BoardMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=BoardMemberRepository::class)
 */
class BoardMember
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
     * @ORM\ManyToMany(targetEntity=BoardRole::class, mappedBy="boardMembers")
     */
    private $boardRoles;

    /**
     * @ORM\ManyToMany(targetEntity=Agenda::class, mappedBy="boardmembers")
     */
    private $agendas;

    /**
     * @ORM\ManyToMany(targetEntity=Board::class, inversedBy="boardMembers")
     */
    private $boards;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="boardMembers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    public function __construct()
    {
        $this->boardRoles = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->boards = new ArrayCollection();
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
     * @return Collection|BoardRole[]
     */
    public function getBoardRoles(): Collection
    {
        return $this->boardRoles;
    }

    public function addBoardRole(BoardRole $boardRole): self
    {
        if (!$this->boardRoles->contains($boardRole)) {
            $this->boardRoles[] = $boardRole;
            $boardRole->addBoardMember($this);
        }

        return $this;
    }

    public function removeBoardRole(BoardRole $boardRole): self
    {
        if ($this->boardRoles->removeElement($boardRole)) {
            $boardRole->removeBoardMember($this);
        }

        return $this;
    }

    /**
     * @return Collection|Agenda[]
     */
    public function getAgendas(): Collection
    {
        return $this->agendas;
    }

    public function addAgenda(Agenda $agenda): self
    {
        if (!$this->agendas->contains($agenda)) {
            $this->agendas[] = $agenda;
            $agenda->addBoardmember($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            $agenda->removeBoardmember($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
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

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }
}
