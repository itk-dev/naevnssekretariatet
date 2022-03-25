<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
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
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     * @Groups({"mail_template"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mail_template"})
     */
    private $phoneNumber;

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
     * @ORM\Column(type="string", length=32)
     */
    private $identifierType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $identifier;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->address = new Address();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }

    public function getAddress(): Address
    {
        return $this->address;
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
            'identifierType',
            'identifier',
            'address',
            'phoneNumber',
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

    public function getIdentifierType(): ?string
    {
        return $this->identifierType;
    }

    public function setIdentifierType(string $identifierType): self
    {
        $this->identifierType = $identifierType;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}
