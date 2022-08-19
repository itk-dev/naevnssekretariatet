<?php

namespace App\Service;

abstract class CaseDeadlineStatuses
{
    public const HEARING_RESPONSE_DEADLINE_EXCEEDED = 1;
    public const HEARING_DEADLINE_EXCEEDED = 2;
    public const PROCESS_DEADLINE_EXCEEDED = 3;
    public const SOME_DEADLINE_EXCEEDED = 4;
    public const ALL_DEADLINES_EXCEEDED = 5;
    public const NO_DEADLINES_EXCEEDED = 6;
}
