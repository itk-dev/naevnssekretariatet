<?php

namespace App\Service;

abstract class CaseSpecialFilterStatuses
{
    public final const IN_HEARING = 1;
    public final const NEW_HEARING_POST = 2;
    public final const ON_AGENDA = 3;
    public final const ACTIVE = 4;
    public final const NOT_ACTIVE = 5;
}
