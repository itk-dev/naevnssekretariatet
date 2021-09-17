<?php

namespace App\Form\Model;

class CaseStatusFormModel
{
    private $status;

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
