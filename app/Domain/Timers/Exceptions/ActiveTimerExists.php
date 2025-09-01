<?php

namespace App\Domain\Timers\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class ActiveTimerExists extends ApiException
{
    public function __construct(string $timerId)
    {
        parent::__construct(
            409,
            'Another timer is already running.',
            ['active_timer_id' => $timerId]
        );
    }
}
