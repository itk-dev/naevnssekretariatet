<?php

namespace App\Entity;

use App\Repository\BoardRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=BoardRoleRepository::class)
 */
class BoardRole
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
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="boardRoles")
     */
    private $boardMembers;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="boardRoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    public function __construct()
    {
        $this->boardMembers = new ArrayCollection();
    }

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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
        }

        return $this;
    }

    public function removeBoardMember(BoardMember $boardMember): self
    {
        $this->boardMembers->removeElement($boardMember);

        return $this;
    }

    public function __toString()
    {
        return $this->board->getName().' '.$this->getTitle();
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
}
