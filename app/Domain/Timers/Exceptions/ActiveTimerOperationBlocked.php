<?php

namespace App\Domain\Timers\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class ActiveTimerOperationBlocked extends ApiException
{
    public function __construct(string $scope, int $runningTimers)
    {
        $message = match ($scope) {
            'project' => 'Cannot perform this project action while timers are still running.',
            'goal' => 'Cannot perform this goal action while timers are still running.',
            'task' => 'Cannot perform this task action while timers are still running.',
            default => 'Active timers are blocking this action.',
        };

        parent::__construct(
            409,
            $message,
            [
                'scope' => $scope,
                'running_timers' => $runningTimers,
            ]
        );
    }
}
