<?php

namespace App\Entity;

use App\Repository\BoardRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=BoardRoleRepository::class)
 */
class BoardRole implements \Stringable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $title = null;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="boardRoles")
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $boardMembers;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="boardRoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?\App\Entity\Board $board = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->boardMembers = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function __toString(): string
    {
        return $this->board->__toString().' '.$this->getTitle();
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
