<?php

namespace App\Entity;

use App\Repository\AgendaProtocolRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AgendaProtocolRepository::class)]
class AgendaProtocol
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $protocol = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }
}
