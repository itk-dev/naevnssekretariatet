<?php

namespace App\Service;

abstract class ReminderStatus
{
    public const Pending = 1;
    public const Overdue = 2;
    public const Completed = 3;
}
