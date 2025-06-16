<?php

namespace App\Domain\Projects\Contracts;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;
    public function findById(string $id): ?Project;
    public function getAllByUserId(string $id): Collection;
}
