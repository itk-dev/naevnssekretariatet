<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Repository\ResidentComplaintBoardCaseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ResidentComplaintBoardCaseRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class ResidentComplaintBoardCase extends CaseEntity
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mail_template"})
     */
    private $leaseSize;

    /**
     * @ORM\Column(type="integer")
     */
    private $complainantPhone;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"mail_template"})
     */
    private $hasVacated;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     * @Groups({"mail_template"})
     */
    private $leaseAddress;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $leaseStarted;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leaseAgreedRent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $leaseInteriorMaintenance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leaseRegulatedRent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leaseRentAtCollectionTime;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $leaseSecurityDeposit;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $previousCasesAtLease;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $prepaidRent;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $feePaid;

    public function __construct()
    {
        parent::__construct();
        $this->leaseAddress = new Address();
    }

    public function getLeaseSize(): ?int
    {
        return $this->leaseSize;
    }

    public function setLeaseSize(?int $leaseSize): self
    {
        $this->leaseSize = $leaseSize;

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

    public function getHasVacated(): ?bool
    {
        return $this->hasVacated;
    }

    public function setHasVacated(bool $hasVacated): self
    {
        $this->hasVacated = $hasVacated;

        return $this;
    }

    public function getLeaseAddress(): Address
    {
        return $this->leaseAddress;
    }

    public function setLeaseAddress(Address $address): void
    {
        $this->leaseAddress = $address;
    }

    public function getLeaseStarted(): ?\DateTimeInterface
    {
        return $this->leaseStarted;
    }

    public function setLeaseStarted(?\DateTimeInterface $leaseStarted): self
    {
        $this->leaseStarted = $leaseStarted;

        return $this;
    }

    public function getLeaseAgreedRent(): ?int
    {
        return $this->leaseAgreedRent;
    }

    public function setLeaseAgreedRent(?int $leaseAgreedRent): self
    {
        $this->leaseAgreedRent = $leaseAgreedRent;

        return $this;
    }

    public function getLeaseInteriorMaintenance(): ?string
    {
        return $this->leaseInteriorMaintenance;
    }

    public function setLeaseInteriorMaintenance(?string $leaseInteriorMaintenance): self
    {
        $this->leaseInteriorMaintenance = $leaseInteriorMaintenance;

        return $this;
    }

    public function getLeaseRegulatedRent(): ?int
    {
        return $this->leaseRegulatedRent;
    }

    public function setLeaseRegulatedRent(?int $leaseRegulatedRent): self
    {
        $this->leaseRegulatedRent = $leaseRegulatedRent;

        return $this;
    }

    public function getLeaseRentAtCollectionTime(): ?int
    {
        return $this->leaseRentAtCollectionTime;
    }

    public function setLeaseRentAtCollectionTime(?int $leaseRentAtCollectionTime): self
    {
        $this->leaseRentAtCollectionTime = $leaseRentAtCollectionTime;

        return $this;
    }

    public function getLeaseSecurityDeposit(): ?int
    {
        return $this->leaseSecurityDeposit;
    }

    public function setLeaseSecurityDeposit(?int $leaseSecurityDeposit): self
    {
        $this->leaseSecurityDeposit = $leaseSecurityDeposit;

        return $this;
    }

    public function getPreviousCasesAtLease(): ?string
    {
        return $this->previousCasesAtLease;
    }

    public function setPreviousCasesAtLease(?string $previousCasesAtLease): self
    {
        $this->previousCasesAtLease = $previousCasesAtLease;

        return $this;
    }

    public function getPrepaidRent(): ?int
    {
        return $this->prepaidRent;
    }

    public function setPrepaidRent(?int $prepaidRent): self
    {
        $this->prepaidRent = $prepaidRent;

        return $this;
    }

    public function getFeePaid(): ?bool
    {
        return $this->feePaid;
    }

    public function setFeePaid(bool $feePaid): self
    {
        $this->feePaid = $feePaid;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateSortingAddress()
    {
        $this->setSortingAddress($this->getLeaseAddress()->__toString());
    }

    public function getNonRelevantComplainantPropertiesWithRespectToValidation(): array
    {
        return [
            'complainantPhone',
        ];
    }
}
