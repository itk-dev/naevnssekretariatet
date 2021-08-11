<?php

namespace App\Logging;

interface LoggableEntityInterface
{
    public function getLoggableProperties(): array;
}
