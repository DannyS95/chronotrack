<?php

namespace App\Domain\Common\Enums;

enum ComparisonOperator: string
{
    case Equal = '=';
    case NotEqual = '!=';
}
