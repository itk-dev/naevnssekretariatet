<?php

namespace App\Service;

use App\Entity\Board;

class BoardHelper
{
    public function getStatusesByBoard(Board $board): array
    {
        $rawStatuses = explode(
            PHP_EOL,
            $board->getStatuses()
        );

        $trimmedStatuses = [];
        foreach ($rawStatuses as $rawStatus) {
            $trimmedStatuses[$rawStatus] = trim($rawStatus);
        }

        return $trimmedStatuses;
    }
}
