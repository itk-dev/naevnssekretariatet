<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\AgendaRepository;
use App\Service\AgendaStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 */
class Agenda implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $status = AgendaStatus::OPEN;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="agendas")
     */
    private $boardmembers;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $remarks;

    /**
     * @ORM\OneToMany(targetEntity=AgendaItem::class, mappedBy="agenda")
     * @ORM\OrderBy({"startTime" = "ASC"})
     */
    private $agendaItems;

    /**
     * @ORM\OneToOne(targetEntity=AgendaProtocol::class, cascade={"persist", "remove"})
     */
    private $protocol;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="agendas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
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

    public function getProtocol(): ?AgendaProtocol
    {
        return $this->protocol;
    }

    public function setProtocol(?AgendaProtocol $protocol): self
    {
        $this->protocol = $protocol;

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

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function __toString()
    {
        return 'Agenda '.$this->getDate()->format('d/m/y');
    }

    public function getLoggableProperties(): array
    {
        return [
            'date',
            'start',
            'end',
            'status',
        ];
    }

    public function isReady(): bool
    {
        return AgendaStatus::READY === $this->status;
    }

    public function isFinished(): bool
    {
        return AgendaStatus::FINISHED === $this->status;
    }
}
