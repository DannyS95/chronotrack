<?php

namespace App\Domain\Projects\Enums;

enum ProjectStatus: string
{
    case Active = 'active';
    case Complete = 'complete';
}
