<?php

namespace App\Entity;

use App\Repository\CaseDecisionProposalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidV4Generator;
use Symfony\Component\Uid\UuidV4;

/**
 * @ORM\Entity(repositoryClass=CaseDecisionProposalRepository::class)
 */
class CaseDecisionProposal
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidV4Generator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $decisionProposal;

    public function getId(): ?UuidV4
    {
        return $this->id;
    }

    public function getDecisionProposal(): ?string
    {
        return $this->decisionProposal;
    }

    public function setDecisionProposal(string $decisionProposal): self
    {
        $this->decisionProposal = $decisionProposal;

        return $this;
    }
}
