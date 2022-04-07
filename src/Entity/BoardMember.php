<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Repository\BoardMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
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
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     */
    private $address;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Identification")
     * @Groups({"mail_template"})
     */
    private $identification;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->address = new Address();
        $this->boardRoles = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->identification = new Identification();
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

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getIdentification(): Identification
    {
        return $this->identification;
    }

    public function setIdentification(Identification $identification): void
    {
        $this->identification = $identification;
    }
}
