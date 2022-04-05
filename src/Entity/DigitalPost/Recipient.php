<?php

namespace App\Entity\DigitalPost;

use App\Entity\DigitalPost;
use App\Entity\Embeddable\Address;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=RecipientRepository::class)
 */
class Recipient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=DigitalPost::class, inversedBy="recipients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $digitalPost;

    /**
     * @ORM\Column(type="string", length=255)
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

    /**
     * @ORM\Embedded(class="App\Entity\Embeddable\Address")
     */
    private $address;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function __toString(): string
    {
        return sprintf('%s (%s: %s)', $this->getName(), $this->getIdentifierType(), $this->getIdentifier());
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDigitalPost(): ?DigitalPost
    {
        return $this->digitalPost;
    }

    public function setDigitalPost(?DigitalPost $digitalPost): self
    {
        $this->digitalPost = $digitalPost;

        return $this;
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

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }
}
