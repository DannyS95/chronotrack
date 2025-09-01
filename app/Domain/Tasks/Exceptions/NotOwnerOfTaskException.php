<?php

namespace App\Domain\Tasks\Exceptions;

use App\Domain\Common\Exceptions\ApiException;

final class NotOwnerOfTaskException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            404,
            'Task not found or not owned by this user.'
        );
    }
}
