<?php

namespace App\Service;

use App\Entity\Board;

class BoardHelper
{
    public function getStatusesByBoard(Board $board): array
    {
        return array_filter(array_map('trim', explode(PHP_EOL, $board->getStatuses())));
    }
}
