<?php

namespace App\Service;

abstract class ReminderStatus
{
    public const Pending = 1;
    public const Active = 2;
    public const Exceeded = 3;
}
