<?php

namespace App\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Blameable Trait similar to Gedmo\Blameable\Traits\BlameableEntity, but with
 * real user reference rather than just a username (string).
 */
trait BlameableEntity
{
    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    protected $createdBy;

    /**
     * @var User
     * @Gedmo\Blameable(on="update")
     */
    #[ORM\ManyToOne(targetEntity: \App\Entity\User::class)]
    protected $updatedBy;

    /**
     * Sets createdBy.
     *
     * @return $this
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Returns createdBy.
     *
     * @return User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    /**
     * Sets updatedBy.
     *
     * @return $this
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Returns updatedBy.
     *
     * @return User
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }
}
