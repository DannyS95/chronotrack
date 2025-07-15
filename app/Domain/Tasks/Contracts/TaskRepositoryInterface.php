<?php

namespace App\Domain\Tasks\Contracts;

use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;

interface TaskRepositoryInterface
{
    public function create(CreateTaskDTO $dto): mixed;

    public function getFiltered(TaskFilterDTO $dto): mixed;
}
