<?php

namespace App\Message;

class NewWebformSubmissionMessage
{
    public function __construct(private array $webformSubmission)
    {
    }

    public function getWebformSubmission(): array
    {
        return $this->webformSubmission;
    }
}
