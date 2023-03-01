<?php

namespace App\Entity;

use App\Repository\CaseEventPartyRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=CaseEventPartyRelationRepository::class)
 */
class CaseEventPartyRelation
{
    public const TYPE_SENDER = 'Sender';
    public const TYPE_RECIPIENT = 'Recipient';

    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEvent::class, inversedBy="caseEventPartyRelations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $caseEvent;

    /**
     * @ORM\ManyToOne(targetEntity=Party::class, inversedBy="caseEventPartyRelations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $party;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getCaseEvent(): CaseEvent
    {
        return $this->caseEvent;
    }

    public function setCaseEvent(CaseEvent $caseEvent): self
    {
        $this->caseEvent = $caseEvent;

        return $this;
    }

    public function getParty(): Party
    {
        return $this->party;
    }

    public function setParty(Party $party): self
    {
        $this->party = $party;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
