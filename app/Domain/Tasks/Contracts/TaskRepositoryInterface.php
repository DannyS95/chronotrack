<?php

namespace App\Domain\Tasks\Contracts;

use App\Application\Tasks\DTO\CreateTaskDTO;

interface TaskRepositoryInterface
{
    public function create(CreateTaskDTO $dto): mixed;
}
