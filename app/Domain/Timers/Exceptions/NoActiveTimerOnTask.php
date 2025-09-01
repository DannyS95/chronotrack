<?php

namespace App\Domain\Timers\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class NoActiveTimerOnTask extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            404,
            'No active timer found for this task.'
        );
    }
}
