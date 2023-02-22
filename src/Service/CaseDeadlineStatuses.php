<?php

namespace App\Service;

abstract class CaseDeadlineStatuses
{
    final public const HEARING_RESPONSE_DEADLINE_EXCEEDED = 1;
    final public const HEARING_DEADLINE_EXCEEDED = 2;
    final public const PROCESS_DEADLINE_EXCEEDED = 3;
    final public const SOME_DEADLINE_EXCEEDED = 4;
    final public const ALL_DEADLINES_EXCEEDED = 5;
    final public const NO_DEADLINES_EXCEEDED = 6;
}
