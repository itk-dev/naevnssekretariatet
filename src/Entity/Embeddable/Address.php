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
