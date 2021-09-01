<?php

namespace App\Entity;

use App\Repository\CaseEntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=CaseEntityRepository::class)
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"caseEntity" = "CaseEntity", "residentComplaintBoardCase" = "ResidentComplaintBoardCase", "rentBoardCase" = "RentBoardCase", "fenceReviewCase" = "FenceReviewCase"})
 * @ORM\EntityListeners({"App\Logging\EntityListener\CaseListener"})
 */
abstract class CaseEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Board::class, inversedBy="caseEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $board;

    /**
     * @ORM\ManyToOne(targetEntity=Municipality::class, inversedBy="caseEntities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseType;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=SubBoard::class, inversedBy="caseEntities")
     */
    private $subboard;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $caseNumber;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="assignedCases")
     */
    private $assignedTo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainant;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantZip;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
     */
    private $caseState = 'submitted';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantCPR;

    public function getId(): ?UuidV4
    {
        return $this->id;
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

    public function getCaseType(): ?string
    {
        return $this->caseType;
    }

    public function setCaseType(string $caseType): self
    {
        $this->caseType = $caseType;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSubboard(): ?SubBoard
    {
        return $this->subboard;
    }

    public function setSubboard(?SubBoard $subboard): self
    {
        $this->subboard = $subboard;

        return $this;
    }

    public function getCaseNumber(): ?string
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    public function getComplainant(): ?string
    {
        return $this->complainant;
    }

    public function setComplainant(?string $complainant): self
    {
        $this->complainant = $complainant;

        return $this;
    }

    public function getComplainantAddress(): ?string
    {
        return $this->complainantAddress;
    }

    public function setComplainantAddress(?string $complainantAddress): self
    {
        $this->complainantAddress = $complainantAddress;

        return $this;
    }

    public function getComplainantZip(): ?string
    {
        return $this->complainantZip;
    }

    public function setComplainantZip(?string $complainantZip): self
    {
        $this->complainantZip = $complainantZip;

        return $this;
    }

    public function getCaseState(): ?string
    {
        return $this->caseState;
    }

    public function setCaseState(string $caseState): self
    {
        $this->caseState = $caseState;

        return $this;
    }

    public function getComplainantCPR(): ?string
    {
        return $this->complainantCPR;
    }

    public function setComplainantCPR(string $complainantCPR): self
    {
        $this->complainantCPR = $complainantCPR;

        return $this;
    }
}
