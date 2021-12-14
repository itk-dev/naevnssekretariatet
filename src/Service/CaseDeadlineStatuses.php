<?php

namespace App\Service;

abstract class CaseDeadlineStatuses
{
    public const HEARING_DEADLINE_EXCEEDED = 1;
    public const PROCESS_DEADLINE_EXCEEDED = 2;
    public const BOTH_DEADLINES_EXCEEDED = 3;
    public const NO_DEADLINES_EXCEEDED = 4;
}
