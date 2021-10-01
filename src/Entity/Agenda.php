<?php

namespace App\Entity;

use App\Repository\AgendaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 */
class Agenda
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $end;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="agendas")
     */
    private $boardmembers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remarks;

    /**
     * @ORM\ManyToOne(targetEntity=SubBoard::class, inversedBy="agendas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subBoard;

    /**
     * @ORM\OneToMany(targetEntity=AgendaItem::class, mappedBy="agenda")
     * @ORM\OrderBy({"startTime" = "ASC"})
     */
    private $agendaItems;

    public function __construct()
    {
        $this->boardmembers = new ArrayCollection();
        $this->agendaItems = new ArrayCollection();
    }

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|BoardMember[]
     */
    public function getBoardmembers(): Collection
    {
        return $this->boardmembers;
    }

    public function addBoardmember(BoardMember $boardmember): self
    {
        if (!$this->boardmembers->contains($boardmember)) {
            $this->boardmembers[] = $boardmember;
        }

        return $this;
    }

    public function removeBoardmember(BoardMember $boardmember): self
    {
        $this->boardmembers->removeElement($boardmember);

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function getSubBoard(): ?SubBoard
    {
        return $this->subBoard;
    }

    public function setSubBoard(?SubBoard $subBoard): self
    {
        $this->subBoard = $subBoard;

        return $this;
    }

    /**
     * @return Collection|AgendaItem[]
     */
    public function getAgendaItems(): Collection
    {
        return $this->agendaItems;
    }

    public function addAgendaItem(AgendaItem $agendaItem): self
    {
        if (!$this->agendaItems->contains($agendaItem)) {
            $this->agendaItems[] = $agendaItem;
            $agendaItem->setAgenda($this);
        }

        return $this;
    }

    public function removeAgendaItem(AgendaItem $agendaItem): self
    {
        if ($this->agendaItems->removeElement($agendaItem)) {
            // set the owning side to null (unless already changed)
            if ($agendaItem->getAgenda() === $this) {
                $agendaItem->setAgenda(null);
            }
        }

        return $this;
    }
}
