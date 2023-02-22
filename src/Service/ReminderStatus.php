<?php

namespace App\Service;

abstract class ReminderStatus
{
    final public const PENDING = 1;
    final public const ACTIVE = 2;
    final public const EXCEEDED = 3;
}
