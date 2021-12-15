<?php

namespace App\Entity\Embeddable;

use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class Address implements LoggableEntityInterface
{
    /**
     * @ORM\Column(type="string")
     */
    private $street;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $floor;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $side;

    /**
     * @ORM\Column(type="integer")
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string")
     */
    private $city;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $bbrData;

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): void
    {
        $this->number = $number;
    }

    public function getFloor(): ?string
    {
        return $this->floor;
    }

    public function setFloor(string $floor): void
    {
        $this->floor = $floor;
    }

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(string $side): void
    {
        $this->side = $side;
    }

    public function getPostalCode(): int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getBbrData(): ?array
    {
        return $this->bbrData;
    }

    public function setBbrData(?array $bbrData): self
    {
        $this->bbrData = $bbrData;

        return $this;
    }

    public function __toString(): string
    {
        $address = $this->getStreet();

        $address .= ' '.$this->getNumber();

        $address .= $this->getFloor()
            ? ', '.$this->getFloor()
            : ''
        ;

        $address .= $this->getSide()
            ? ' '.$this->getSide()
            : ''
        ;

        $address .= ', '.$this->getPostalCode();
        $address .= ' '.$this->getCity();

        return $address;
    }

    public function getLoggableProperties(): array
    {
        return [
            'street',
            'number',
            'side',
            'floor',
            'postalCode',
            'city',
        ];
    }
}
