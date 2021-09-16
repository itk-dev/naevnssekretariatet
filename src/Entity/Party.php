<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\PartyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=PartyRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\PartyListener"})
 */
class Party implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $journalNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPartOfPartIndex;

    /**
     * @ORM\OneToMany(targetEntity="CasePartyRelation", mappedBy="party")
     */
    private $casePartyRelation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cpr;

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getJournalNumber(): ?string
    {
        return $this->journalNumber;
    }

    public function setJournalNumber(?string $journalNumber): self
    {
        $this->journalNumber = $journalNumber;

        return $this;
    }

    public function getIsPartOfPartIndex(): ?bool
    {
        return $this->isPartOfPartIndex;
    }

    public function setIsPartOfPartIndex(bool $isPartOfPartIndex): self
    {
        $this->isPartOfPartIndex = $isPartOfPartIndex;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getLoggableProperties(): array
    {
        return [
            'name',
            'CPR',
            'address',
            'phoneNumber',
            'journalNumber',
        ];
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

    public function getCpr(): ?string
    {
        return $this->cpr;
    }

    public function setCpr(string $cpr): self
    {
        $this->cpr = $cpr;

        return $this;
    }
}
