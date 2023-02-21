<?php

namespace App\Message;

class NewWebformSubmissionMessage
{
    public function __construct(private readonly array $webformSubmission)
    {
    }

    public function getWebformSubmission(): array
    {
        return $this->webformSubmission;
    }
}
