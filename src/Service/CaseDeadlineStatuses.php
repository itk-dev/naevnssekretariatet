<?php

namespace App\Service;

abstract class CaseDeadlineStatuses
{
    public const SOME_DEADLINE_EXCEEDED = 1;
    public const HEARING_DEADLINE_EXCEEDED = 2;
    public const PROCESS_DEADLINE_EXCEEDED = 3;
    public const BOTH_DEADLINES_EXCEEDED = 4;
    public const NO_DEADLINES_EXCEEDED = 5;
}
