<?php

namespace App\Entity;

use App\Repository\ResidentComplaintBoardCaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResidentComplaintBoardCaseRepository::class)
 */
class ResidentComplaintBoardCase extends CaseEntity
{
    /**
     * @ORM\Column(type="integer")
     */
    private $leaseSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $complainant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $complainantAddress;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $complainantPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $complainantZip;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
     */
    private $caseState = 'submitted';

    public function getLeaseSize(): ?int
    {
        return $this->leaseSize;
    }

    public function setLeaseSize(?int $leaseSize): self
    {
        $this->leaseSize = $leaseSize;

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

    public function getComplainantPhone(): ?int
    {
        return $this->complainantPhone;
    }

    public function setComplainantPhone(?int $complainantPhone): self
    {
        $this->complainantPhone = $complainantPhone;

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
}
