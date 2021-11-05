<?php

namespace App\Service;

abstract class AgendaStatus
{
    public const Open = 1;
    public const Full = 2;
    public const Ready = 3;
    public const Finished = 4;
    public const Not_finished = 5;
}
