<?php

namespace App\Service;

abstract class CaseSpecialFilterStatuses
{
    final public const IN_HEARING = 1;
    final public const NEW_HEARING_POST = 2;
    final public const ON_AGENDA = 3;
    final public const ACTIVE = 4;
    final public const NOT_ACTIVE = 5;
}
