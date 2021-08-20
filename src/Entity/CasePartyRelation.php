<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity
 * @ORM\Table(name="case_parties")
 * @ORM\EntityListeners({"App\Logging\EntityListener\CasePartyRelationListener"})
 */
class CasePartyRelation implements LoggableEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="casePartyRelation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $case;

    /**
     * @ORM\ManyToOne(targetEntity="Party", inversedBy="casePartyRelation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $party;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function getId(): ?UuidV4
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