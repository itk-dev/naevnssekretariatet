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
    private $size;

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
    private $complainantPostalCode;

    /**
     * @ORM\Column(type="string", length=255, options={"default": "submitted"})
     */
    private $caseState = 'submitted';

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

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

    public function getComplainantPostalCode(): ?string
    {
        return $this->complainantPostalCode;
    }

    public function setComplainantPostalCode(?string $complainantPostalCode): self
    {
        $this->complainantPostalCode = $complainantPostalCode;

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
