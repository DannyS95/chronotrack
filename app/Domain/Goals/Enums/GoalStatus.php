<?php

namespace App\Domain\Goals\Enums;

enum GoalStatus: string
{
    case ACTIVE   = 'active';
    case DORMANT  = 'dormant';
    case COMPLETE = 'complete';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
