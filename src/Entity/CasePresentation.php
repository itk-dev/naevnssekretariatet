<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\CasePresentationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=CasePresentationRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\CasePresentationListener"})
 */
class CasePresentation implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private \Symfony\Component\Uid\UuidV4 $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $presentation = null;

    /**
     * @ORM\OneToOne(targetEntity=CaseEntity::class, mappedBy="presentation", cascade={"persist", "remove"})
     */
    private ?\App\Entity\CaseEntity $caseEntity = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPresentation(): ?string
    {
        return $this->presentation;
    }

    public function setPresentation(?string $presentation): self
    {
        $this->presentation = $presentation;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'presentation',
        ];
    }

    public function getCaseEntity(): ?CaseEntity
    {
        return $this->caseEntity;
    }

    public function setCaseEntity(?CaseEntity $caseEntity): self
    {
        // unset the owning side of the relation if necessary
        if (null === $caseEntity && null !== $this->caseEntity) {
            $this->caseEntity->setPresentation(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $caseEntity && $caseEntity->getPresentation() !== $this) {
            $caseEntity->setPresentation($this);
        }

        $this->caseEntity = $caseEntity;

        return $this;
    }
}
