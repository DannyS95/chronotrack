<?php

namespace App\Domain\Timers\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class ActiveTimerExists extends ApiException
{
    public function __construct(string $timerId, string $scope = 'timer')
    {
        $message = match ($scope) {
            'task' => 'A timer is already running on this task.',
            'goal' => 'A timer is already running for this goal.',
            'project' => 'A timer is already running for this project.',
            default => 'Another timer is already running.',
        };

        parent::__construct(
            409,
            $message,
            [
                'active_timer_id' => $timerId,
                'scope' => $scope,
            ]
        );
    }
}
