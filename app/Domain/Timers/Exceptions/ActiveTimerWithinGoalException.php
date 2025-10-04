<?php

namespace App\Domain\Timers\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class ActiveTimerWithinGoalException extends ApiException
{
    public function __construct(string $timerId)
    {
        parent::__construct(
            409,
            'Another timer is already running for this goal.',
            ['active_timer_id' => $timerId]
        );
    }
}
