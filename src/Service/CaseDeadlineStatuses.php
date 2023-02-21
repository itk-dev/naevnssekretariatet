<?php

namespace App\Service;

abstract class CaseDeadlineStatuses
{
    public final const HEARING_RESPONSE_DEADLINE_EXCEEDED = 1;
    public final const HEARING_DEADLINE_EXCEEDED = 2;
    public final const PROCESS_DEADLINE_EXCEEDED = 3;
    public final const SOME_DEADLINE_EXCEEDED = 4;
    public final const ALL_DEADLINES_EXCEEDED = 5;
    public final const NO_DEADLINES_EXCEEDED = 6;
}
