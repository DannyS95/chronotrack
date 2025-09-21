<?php

namespace App\Domain\Goals\Contracts;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use Illuminate\Support\Collection;

interface GoalRepositoryInterface
{
    public function create(array $data): Goal;

    public function list(array $filters): Collection;
}
