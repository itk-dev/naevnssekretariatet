<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'case_parties')]
#[ORM\Entity]
#[ORM\EntityListeners([\App\Logging\EntityListener\CasePartyRelationListener::class])]
class CasePartyRelation implements LoggableEntityInterface
{
    use SoftDeletableEntity;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly \Symfony\Component\Uid\UuidV4 $id;

    #[ORM\ManyToOne(targetEntity: 'CaseEntity', inversedBy: 'casePartyRelation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\CaseEntity $case = null;

    #[ORM\ManyToOne(targetEntity: 'Party', inversedBy: 'casePartyRelation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\Party $party = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $type = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCase(): ?CaseEntity
    {
        return $this->case;
    }

    public function setCase(CaseEntity $case): self
    {
        $this->case = $case;

        return $this;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(Party $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'type',
        ];
    }
}
