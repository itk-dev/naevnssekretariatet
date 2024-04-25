<?php

namespace App\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait CustomDataTrait
{
    /**
     * @ORM\Column(type="json")
     *
     * @Groups({"mail_template"})
     */
    private $customData = [];

    public function getCustomData(): ?array
    {
        return $this->customData;
    }

    public function setCustomData(?array $customData): self
    {
        $this->customData = $customData;

        return $this;
    }
}
