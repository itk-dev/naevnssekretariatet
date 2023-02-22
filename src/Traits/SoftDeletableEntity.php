<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeletableEntity
{
    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private $softDeleted = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $softDeletedAt;

    public function setSoftDeletedAt(\DateTime $softDeletedAt = null): self
    {
        $this->softDeletedAt = $softDeletedAt;

        return $this;
    }

    public function getSoftDeletedAt(): ?\DateTime
    {
        return $this->softDeletedAt;
    }

    public function getSoftDeleted(): ?bool
    {
        return $this->softDeleted;
    }

    public function setSoftDeleted(bool $softDeleted): self
    {
        $this->softDeleted = $softDeleted;

        return $this;
    }
}
