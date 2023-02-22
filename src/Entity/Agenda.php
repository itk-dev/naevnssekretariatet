<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\AgendaRepository;
use App\Service\AgendaStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=AgendaRepository::class)
 */
class Agenda implements LoggableEntityInterface, \Stringable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?\DateTimeInterface $start = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?\DateTimeInterface $end = null;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     * @Groups({"mail_template"})
     */
    private int $status = AgendaStatus::OPEN;

    /**
     * @ORM\ManyToMany(targetEntity=BoardMember::class, inversedBy="agendas")
     * @Groups({"mail_template"})
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $boardmembers;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?string $remarks = null;

    /**
     * @ORM\OneToMany(targetEntity=AgendaItem::class, mappedBy="agenda")
     * @ORM\OrderBy({"startTime" = "ASC"})
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $agendaItems;

    /**
     * @ORM\OneToOne(targetEntity=AgendaProtocol::class, cascade={"persist", "remove"})
     */
    private ?\App\Entity\AgendaProtocol $protocol = null;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="agendas")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private ?\App\Entity\Board $board = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isPublished = false;

    /**
     * @ORM\OneToMany(targetEntity=AgendaBroadcast::class, mappedBy="agenda", orphanRemoval=true)
     */
    private \Doctrine\Common\Collections\ArrayCollection|array $agendaBroadcasts;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?string $agendaMeetingPoint = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->boardmembers = new ArrayCollection();
        $this->agendaItems = new ArrayCollection();
        $this->agendaBroadcasts = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function __toString(): string
    {
        return $this->date ? 'Agenda '.$this->date->format('d/m/y') : 'Agenda';
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

    /**
     * @return Collection|AgendaBroadcast[]
     */
    public function getAgendaBroadcasts(): Collection
    {
        return $this->agendaBroadcasts;
    }

    public function addAgendaBroadcast(AgendaBroadcast $agendaBroadcast): self
    {
        if (!$this->agendaBroadcasts->contains($agendaBroadcast)) {
            $this->agendaBroadcasts[] = $agendaBroadcast;
            $agendaBroadcast->setAgenda($this);
        }

        return $this;
    }

    public function removeAgendaBroadcast(AgendaBroadcast $agendaBroadcast): self
    {
        if ($this->agendaBroadcasts->removeElement($agendaBroadcast)) {
            // set the owning side to null (unless already changed)
            if ($agendaBroadcast->getAgenda() === $this) {
                $agendaBroadcast->setAgenda(null);
            }
        }

        return $this;
    }

    public function getAgendaMeetingPoint(): ?string
    {
        return $this->agendaMeetingPoint;
    }

    public function setAgendaMeetingPoint(?string $agendaMeetingPoint): self
    {
        $this->agendaMeetingPoint = $agendaMeetingPoint;

        return $this;
    }
}
