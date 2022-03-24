<?php

namespace App\Entity;

use App\Repository\BoardMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=BoardMemberRepository::class)
 * @UniqueEntity(fields={"cpr"})
 */
class BoardMember
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
     * @ORM\ManyToMany(targetEntity=BoardRole::class, mappedBy="boardMembers")
     */
    private $boardRoles;

    /**
     * @ORM\ManyToMany(targetEntity=Agenda::class, mappedBy="boardmembers")
     */
    private $agendas;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cpr;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->boardRoles = new ArrayCollection();
        $this->agendas = new ArrayCollection();
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

    public function getCpr(): ?string
    {
        return $this->cpr;
    }

    public function setCpr(string $cpr): self
    {
        $this->cpr = $cpr;

        return $this;
    }
}
