<?php

namespace App\Service;

abstract class AgendaStatus
{
    final public const OPEN = 1;
    final public const FULL = 2;
    final public const READY = 3;
    final public const FINISHED = 4;
    final public const NOT_FINISHED = 5;
}
