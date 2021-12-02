<?php

namespace App\Service;

abstract class AgendaStatus
{
    public const OPEN = 1;
    public const FULL = 2;
    public const READY = 3;
    public const FINISHED = 4;
    public const NOT_FINISHED = 5;
}
