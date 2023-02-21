<?php

namespace App\Entity\Embeddable;

use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class Identification implements LoggableEntityInterface
{
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $identifier;

    /**
     * @see https://www.billy.dk/billypedia/p-nummer/
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $pNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier ?? '';
    }

    public function setIdentifier(?string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getPNumber(): ?string
    {
        return $this->pNumber;
    }

    public function setPNumber(?string $pNumber): self
    {
        $this->pNumber = $pNumber;

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

    public function getLoggableProperties(): array
    {
        return [
            'type',
            'identifier',
            'validatedAt',
        ];
    }
}
