<?php

namespace App\Domain\Tasks\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;

interface TaskRepositoryInterface
{
    public function create(array $data): mixed;

    public function getFiltered(array $filters, string $userId): Builder;

    public function userOwnsTask(string $taskId, int $userId): bool;
}
