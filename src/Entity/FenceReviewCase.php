<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Repository\FenceReviewCaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FenceReviewCaseRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class FenceReviewCase extends CaseEntity
{
    /**
     * @ORM\Column(type="text")
     */
    private $conditions;

    /**
     * @ORM\Column(type="text")
     */
    private $bringerClaim;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bringerCadastralNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accused;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     */
    private $accusedAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accusedCadastralNumber;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Identification")
     */
    private $accusedIdentification;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $accusedIsUnderAddressProtection = false;

    public function __construct()
    {
        parent::__construct();
        $this->accusedAddress = new Address();
        $this->accusedIdentification = new Identification();
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(string $conditions): self
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getBringerClaim(): ?string
    {
        return $this->bringerClaim;
    }

    public function setBringerClaim(string $bringerClaim): self
    {
        $this->bringerClaim = $bringerClaim;

        return $this;
    }

    public function getBringerCadastralNumber(): ?string
    {
        return $this->bringerCadastralNumber;
    }

    public function setBringerCadastralNumber(string $bringerCadastralNumber): self
    {
        $this->bringerCadastralNumber = $bringerCadastralNumber;

        return $this;
    }

    public function getAccused(): ?string
    {
        return $this->accused;
    }

    public function setAccused(string $accused): self
    {
        $this->accused = $accused;

        return $this;
    }

    public function getAccusedCadastralNumber(): ?string
    {
        return $this->accusedCadastralNumber;
    }

    public function setAccusedCadastralNumber(string $accusedCadastralNumber): self
    {
        $this->accusedCadastralNumber = $accusedCadastralNumber;

        return $this;
    }

    public function getAccusedAddress(): Address
    {
        return $this->accusedAddress;
    }

    public function setAccusedAddress(Address $address): void
    {
        $this->accusedAddress = $address;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateSortingAddress()
    {
        $this->setSortingAddress($this->getBringerAddress()->__toString());
    }

    public function getAccusedIdentification(): Identification
    {
        return $this->accusedIdentification;
    }

    public function setAccusedIdentification(Identification $accusedIdentification): void
    {
        $this->accusedIdentification = $accusedIdentification;
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
            'accusedIdentification' => [
                'accused',
                'accusedIdentification.type',
                'accusedIdentification.identifier',
                'accusedAddress.street',
                'accusedAddress.number',
                'accusedAddress.floor',
                'accusedAddress.side',
                'accusedAddress.postalCode',
                'accusedAddress.city',
            ],
        ];
    }

    public function accusedIsUnderAddressProtection(): ?bool
    {
        return $this->accusedIsUnderAddressProtection;
    }

    public function setAccusedIsUnderAddressProtection(bool $accusedIsUnderAddressProtection): self
    {
        $this->accusedIsUnderAddressProtection = $accusedIsUnderAddressProtection;

        return $this;
    }
}
