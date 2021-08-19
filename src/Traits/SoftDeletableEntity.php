<?php

namespace App\Traits;

trait SoftDeletableEntity
{
    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $softDeleted = false;


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