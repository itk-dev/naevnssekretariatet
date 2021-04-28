<?php

namespace App\Entity;

use App\Repository\SubBoardRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=SubBoardRepository::class)
 */
class SubBoard
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
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="subBoards")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mainBoard;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="subBoards")
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

    public function getMainBoard(): ?Board
    {
        return $this->mainBoard;
    }

    public function setMainBoard(?Board $mainBoard): self
    {
        $this->mainBoard = $mainBoard;

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
