<?php

namespace App\Domain\Projects\Enums;

enum ProjectCompletionSource: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';
}
