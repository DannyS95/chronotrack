<?php

namespace App\Domain\Projects\Contracts;

use App\Application\Projects\DTO\ProjectFilterDTO;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Database\Eloquent\Builder;

interface ProjectRepositoryInterface
{
    public function create(array $data): Project;
    public function findById(string $id): ?Project;
    public function getAll(array $id): Builder;

    public function findOwned(string $projectId, string $userId): Project;

    public function delete(Project $project): void;

    public function markComplete(Project $project, string $source): Project;

    public function markActive(Project $project): Project;
}
