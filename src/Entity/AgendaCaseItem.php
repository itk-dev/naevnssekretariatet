<?php

namespace App\Entity;

use App\Repository\AgendaCaseItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendaCaseItemRepository::class)
 */
class AgendaCaseItem extends AgendaItem
{
    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $inspection = false;

    /**
     * @ORM\ManyToOne(targetEntity=CaseEntity::class)
     */
    private $caseEntity;

    public function getInspection(): ?bool
    {
        return $this->inspection;
    }

    public function setInspection(bool $inspection): self
    {
        $this->inspection = $inspection;

        return $this;
    }

    public function getCaseEntity(): ?CaseEntity
    {
        return $this->caseEntity;
    }

    public function setCaseEntity(?CaseEntity $caseEntity): self
    {
        $this->caseEntity = $caseEntity;

        return $this;
    }
}
