<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\DocumentTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=DocumentTypeRepository::class)
 */
class UploadedDocumentType implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function __toString()
    {
        return $this->name;
    }

    public function getLoggableProperties(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
