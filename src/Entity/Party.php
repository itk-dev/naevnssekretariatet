<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Identification;
use App\Logging\LoggableEntityInterface;
use App\Repository\PartyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PartyRepository::class)
 *
 * @ORM\EntityListeners({"App\Logging\EntityListener\PartyListener"})
 */
class Party implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     *
     * @Groups({"mail_template"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     *
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
     *
     * @Groups({"mail_template"})
     */
    private $name;

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Identification")
     *
     * @Groups({"mail_template"})
     *
     * @Assert\Valid()
     */
    private $identification;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $isUnderAddressProtection = false;

    /**
     * Virtual property set on runtime.
     */
    private ?bool $canReceiveDigitalPost = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->address = new Address();
        $this->identification = new Identification();
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
            'identification',
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

    public function getIdentification(): Identification
    {
        return $this->identification;
    }

    public function setIdentification(Identification $identification): void
    {
        $this->identification = $identification;
    }

    public function getIsUnderAddressProtection(): ?bool
    {
        return $this->isUnderAddressProtection;
    }

    public function setIsUnderAddressProtection(bool $isUnderAddressProtection): self
    {
        $this->isUnderAddressProtection = $isUnderAddressProtection;

        return $this;
    }

    public function canReceiveDigitalPost(): ?bool
    {
        return $this->canReceiveDigitalPost;
    }

    public function setCanReceiveDigitalPost(bool $canReceiveDigitalPost): self
    {
        $this->canReceiveDigitalPost = $canReceiveDigitalPost;

        return $this;
    }
}
