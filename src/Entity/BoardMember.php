<?php

namespace App\Entity;

use App\Repository\BoardMemberRepository;
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
     * @ORM\Column(type="string", length=255)
     */
    private $az;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="boardMembers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="boardMembers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

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

    public function getAz(): ?string
    {
        return $this->az;
    }

    public function setAz(string $az): self
    {
        $this->az = $az;

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): self
    {
        $this->board = $board;

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
