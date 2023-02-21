<?php

namespace App\Service;

abstract class AgendaStatus
{
    public final const OPEN = 1;
    public final const FULL = 2;
    public final const READY = 3;
    public final const FINISHED = 4;
    public final const NOT_FINISHED = 5;
}
