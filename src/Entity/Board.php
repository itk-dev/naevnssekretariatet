<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\BoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BoardRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\BoardListener"})
 */
class Board implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="boards")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mail_template"})
     */
    private $municipality;

    /**
     * @ORM\OneToMany(targetEntity=CaseEntity::class, mappedBy="board")
     */
    private $caseEntities;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseFormType;

    /**
     * @Assert\Positive
     * @ORM\Column(type="integer")
     */
    private $hearingResponseDeadline;

    /**
     * @ORM\Column(type="text")
     */
    private $statuses;

    /**
     * @ORM\OneToMany(targetEntity=BoardRole::class, mappedBy="board")
     */
    private $boardRoles;

    /**
     * @ORM\Column(type="text")
     */
    private $complainantTypes;

    /**
     * @ORM\Column(type="text")
     */
    private $counterpartyTypes;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="board")
     */
    private $agendas;

    /**
     * @Assert\Positive
     * @ORM\Column(type="integer")
     */
    private $finishProcessingDeadlineDefault;

    /**
     * @Assert\Positive
     * @ORM\Column(type="integer")
     */
    private $finishHearingDeadlineDefault;

    /**
     * @ORM\ManyToMany(targetEntity=ComplaintCategory::class, mappedBy="boards")
     */
    private $complaintCategories;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->caseEntities = new ArrayCollection();
        $this->boardRoles = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->complaintCategories = new ArrayCollection();
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

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function __toString()
    {
        return $this->name.' '.$this->getMunicipality()->getName();
    }

    /**
     * @return Collection|CaseEntity[]
     */
    public function getCaseEntities(): Collection
    {
        return $this->caseEntities;
    }

    public function addCaseEntity(CaseEntity $caseEntity): self
    {
        if (!$this->caseEntities->contains($caseEntity)) {
            $this->caseEntities[] = $caseEntity;
            $caseEntity->setBoard($this);
        }

        return $this;
    }

    public function removeCaseEntity(CaseEntity $caseEntity): self
    {
        if ($this->caseEntities->removeElement($caseEntity)) {
            // set the owning side to null (unless already changed)
            if ($caseEntity->getBoard() === $this) {
                $caseEntity->setBoard(null);
            }
        }

        return $this;
    }

    public function getCaseFormType(): ?string
    {
        return $this->caseFormType;
    }

    public function setCaseFormType(string $caseFormType): self
    {
        $this->caseFormType = $caseFormType;

        return $this;
    }

    public function getHearingResponseDeadline(): ?int
    {
        return $this->hearingResponseDeadline;
    }

    public function setHearingResponseDeadline(int $hearingResponseDeadline): self
    {
        $this->hearingResponseDeadline = $hearingResponseDeadline;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'name',
            'caseFormType',
            'hearingResponseDeadline',
            'finishProcessingDeadlineDefault',
        ];
    }

    public function getStatuses(): ?string
    {
        return $this->statuses;
    }

    public function setStatuses(string $statuses): self
    {
        $this->statuses = $statuses;

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
            $boardRole->setBoard($this);
        }

        return $this;
    }

    public function removeBoardRole(BoardRole $boardRole): self
    {
        if ($this->boardRoles->removeElement($boardRole)) {
            // set the owning side to null (unless already changed)
            if ($boardRole->getBoard() === $this) {
                $boardRole->setBoard(null);
            }
        }

        return $this;
    }

    public function getComplainantTypes(): ?string
    {
        return $this->complainantTypes;
    }

    public function setComplainantTypes(string $complainantTypes): self
    {
        $this->complainantTypes = $complainantTypes;

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
            $agenda->setBoard($this);
        }

        return $this;
    }

    public function getCounterpartyTypes(): ?string
    {
        return $this->counterpartyTypes;
    }

    public function setCounterpartyTypes(string $counterpartyTypes): self
    {
        $this->counterpartyTypes = $counterpartyTypes;

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getBoard() === $this) {
                $agenda->setBoard(null);
            }
        }

        return $this;
    }

    public function getFinishProcessingDeadlineDefault(): ?int
    {
        return $this->finishProcessingDeadlineDefault;
    }

    public function setFinishProcessingDeadlineDefault(int $finishProcessingDeadlineDefault): self
    {
        $this->finishProcessingDeadlineDefault = $finishProcessingDeadlineDefault;

        return $this;
    }

    public function getFinishHearingDeadlineDefault(): ?int
    {
        return $this->finishHearingDeadlineDefault;
    }

    public function setFinishHearingDeadlineDefault(int $finishHearingDeadlineDefault): self
    {
        $this->finishHearingDeadlineDefault = $finishHearingDeadlineDefault;

        return $this;
    }

    /**
     * @return Collection|ComplaintCategory[]
     */
    public function getComplaintCategories(): Collection
    {
        return $this->complaintCategories;
    }

    public function addComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if (!$this->complaintCategories->contains($complaintCategory)) {
            $this->complaintCategories[] = $complaintCategory;
            $complaintCategory->addBoard($this);
        }

        return $this;
    }

    public function removeComplaintCategory(ComplaintCategory $complaintCategory): self
    {
        if ($this->complaintCategories->removeElement($complaintCategory)) {
            $complaintCategory->removeBoard($this);
        }

        return $this;
    }
}
