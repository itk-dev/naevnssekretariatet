<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Repository\FenceReviewCaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FenceReviewCaseRepository::class)
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
    private $complainantClaim;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $complainantCadastralNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accused;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     */
    private $accusedAddress;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accusedCPR;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accusedCadastralNumber;

    public function __construct()
    {
        parent::__construct();
        $this->accusedAddress = new Address();
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

    public function getComplainantClaim(): ?string
    {
        return $this->complainantClaim;
    }

    public function setComplainantClaim(string $complainantClaim): self
    {
        $this->complainantClaim = $complainantClaim;

        return $this;
    }

    public function getComplainantCadastralNumber(): ?string
    {
        return $this->complainantCadastralNumber;
    }

    public function setComplainantCadastralNumber(string $complainantCadastralNumber): self
    {
        $this->complainantCadastralNumber = $complainantCadastralNumber;

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

    public function getAccusedCPR(): ?string
    {
        return $this->accusedCPR;
    }

    public function setAccusedCPR(string $accusedCPR): self
    {
        $this->accusedCPR = $accusedCPR;

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
}