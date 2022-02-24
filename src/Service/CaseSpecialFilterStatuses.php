<?php

namespace App\Service;

abstract class CaseSpecialFilterStatuses
{
    public const IN_HEARING = 1;
    public const NEW_HEARING_POST = 2;
    public const ON_AGENDA = 3;
    public const ACTIVE = 4;
    public const NOT_ACTIVE = 5;
}
