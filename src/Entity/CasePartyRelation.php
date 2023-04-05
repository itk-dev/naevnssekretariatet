<?php

namespace App\Entity;

use App\Logging\LoggableEntityInterface;
use App\Traits\SoftDeletableEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity
 * @ORM\Table(name="case_parties")
 * @ORM\EntityListeners({"App\Logging\EntityListener\CasePartyRelationListener"})
 */
class CasePartyRelation implements LoggableEntityInterface
{
    use SoftDeletableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"mail_template"})
     */
    private $referenceNumber;

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

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;

        return $this;
    }

    public function getLoggableProperties(): array
    {
        return [
            'type',
        ];
    }
}
