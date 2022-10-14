<?php

namespace App\Entity\Embeddable;

use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Embeddable
 */
class Address implements LoggableEntityInterface
{
    /**
     * @ORM\Column(type="string")
     * @Groups({"mail_template"})
     */
    private $street;

    /**
     * @ORM\Column(type="string")
     * @Groups({"mail_template"})
     */
    private $number;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"mail_template"})
     */
    private $floor;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"mail_template"})
     */
    private $side;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"mail_template"})
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string")
     * @Groups({"mail_template"})
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

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"mail_template"})
     */
    private $extraInformation;

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getFloor(): ?string
    {
        return $this->floor;
    }

    public function setFloor(?string $floor): self
    {
        $this->floor = $floor;

        return $this;
    }

    public function getSide(): ?string
    {
        return $this->side;
    }

    public function setSide(?string $side): self
    {
        $this->side = $side;

        return $this;
    }

    public function getPostalCode(): int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

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

    public function getExtraInformation(): ?string
    {
        return $this->extraInformation;
    }

    public function setExtraInformation(?string $extraInformation): self
    {
        $this->extraInformation = $extraInformation;

        return $this;
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
