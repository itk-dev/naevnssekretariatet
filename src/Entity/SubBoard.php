<?php

namespace App\Entity;

use App\Repository\SubBoardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SubBoardRepository::class)
 */
class SubBoard
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
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

    public function getId(): ?int
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
}
