<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\CasePresentationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=CasePresentationRepository::class)
 * @ORM\EntityListeners({"App\Logging\EntityListener\CasePresentationListener"})
 */
class CasePresentation implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $presentation;

    /**
     * @ORM\OneToOne(targetEntity=CaseEntity::class, mappedBy="presentation", cascade={"persist", "remove"})
     */
    private $caseEntity;

    public function getId(): ?UuidV4
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
