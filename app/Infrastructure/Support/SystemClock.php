<?php

namespace App\Infrastructure\Support;

use App\Domain\Common\Contracts\Clock;

final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
