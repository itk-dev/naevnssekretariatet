<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Repository\CaseDecisionProposalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CaseDecisionProposalRepository::class)]
#[ORM\EntityListeners([\App\Logging\EntityListener\CaseDecisionProposalListener::class])]
class CaseDecisionProposal implements LoggableEntityInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $decisionProposal = null;

    #[ORM\OneToOne(targetEntity: CaseEntity::class, mappedBy: 'decisionProposal', cascade: ['persist', 'remove'])]
    private ?\App\Entity\CaseEntity $caseEntity = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getDecisionProposal(): ?string
    {
        return $this->decisionProposal;
    }

    public function setDecisionProposal(?string $decisionProposal): self
    {
        $this->decisionProposal = $decisionProposal;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'decisionProposal',
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
            $this->caseEntity->setDecisionProposal(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $caseEntity && $caseEntity->getDecisionProposal() !== $this) {
            $caseEntity->setDecisionProposal($this);
        }

        $this->caseEntity = $caseEntity;

        return $this;
    }
}
