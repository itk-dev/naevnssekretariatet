<?php

namespace App\Service;

abstract class ReminderStatus
{
    public final const PENDING = 1;
    public final const ACTIVE = 2;
    public final const EXCEEDED = 3;
}
