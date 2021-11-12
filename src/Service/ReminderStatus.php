<?php

namespace App\Service;

abstract class ReminderStatus
{
    public const PENDING = 1;
    public const ACTIVE = 2;
    public const EXCEEDED = 3;
}
