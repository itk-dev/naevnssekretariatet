<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=SettingsRepository::class)
 */
class Settings
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $deadline;

    /**
     * @ORM\OneToOne(targetEntity=Municipality::class, inversedBy="settings", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $municipality;

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getDeadline(): ?int
    {
        return $this->deadline;
    }

    public function setDeadline(int $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function __toString(): string
    {
        return strval($this->getId());
    }

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(Municipality $municipality): self
    {
        $this->municipality = $municipality;

        return $this;
    }
}
