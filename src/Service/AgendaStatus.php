<?php

namespace App\Service;

abstract class AgendaStatus
{
    public const Open = 1;
    public const Full = 2;
    public const Finished = 3;
    public const Not_finished = 4;
}
