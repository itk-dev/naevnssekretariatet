<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\PartyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=PartyRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\PartyListener"})
 */
class Party implements LoggableEntityInterface
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
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
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
     * @Groups({"mail_template"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cpr;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
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
