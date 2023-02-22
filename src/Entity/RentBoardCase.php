<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Repository\RentBoardCaseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RentBoardCaseRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class RentBoardCase extends CaseEntity
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?int $leaseSize = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?int $bringerPhone = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $hasVacated = null;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     * @Groups({"mail_template"})
     */
    private \App\Entity\Embeddable\Address $leaseAddress;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?\DateTimeInterface $leaseStarted = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"mail_template"})
     */
    private ?int $leaseAgreedRent = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $leaseInteriorMaintenance = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $leaseRegulatedRent = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $leaseRentAtCollectionTime = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $leaseSecurityDeposit = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $previousCasesAtLease = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $prepaidRent = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $feePaid = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $leaseType = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $leaseRegulatedAt = null;

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

    public function getBringerPhone(): ?int
    {
        return $this->bringerPhone;
    }

    public function setBringerPhone(?int $bringerPhone): self
    {
        $this->bringerPhone = $bringerPhone;

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

    public function getLeaseRegulatedRent(): ?bool
    {
        return $this->leaseRegulatedRent;
    }

    public function setLeaseRegulatedRent(?bool $leaseRegulatedRent): self
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

    public function getLeaseType(): ?string
    {
        return $this->leaseType;
    }

    public function setLeaseType(?string $leaseType): self
    {
        $this->leaseType = $leaseType;

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

    public function getIdentificationInvalidationProperties(): array
    {
        return [
            'bringerIdentification' => [
                'bringer',
                'bringerIdentification.type',
                'bringerIdentification.identifier',
                'bringerAddress.street',
                'bringerAddress.number',
                'bringerAddress.floor',
                'bringerAddress.side',
                'bringerAddress.postalCode',
                'bringerAddress.city',
            ],
        ];
    }

    public function getLeaseRegulatedAt(): ?\DateTimeInterface
    {
        return $this->leaseRegulatedAt;
    }

    public function setLeaseRegulatedAt(?\DateTimeInterface $leaseRegulatedAt): self
    {
        $this->leaseRegulatedAt = $leaseRegulatedAt;

        return $this;
    }
}
