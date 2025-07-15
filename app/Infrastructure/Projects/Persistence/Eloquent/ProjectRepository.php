<?php

namespace App\Infrastructure\Projects\Persistence\Eloquent;

use App\Application\Projects\Dto\ProjectFilterDTO;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        $project = Project::create($data);

        $project->users()->sync($data['user_ids'] ?? []);

        return $project;
    }

    public function findById(string $id): ?Project
    {
        return Project::with('users')->find($id);
    }

    public function getAllByuser_id(string $id): Collection
    {
        return Project::where('user_id', $id)->latest()->get();
    }

    public function getAll(ProjectFilterDTO $filters): Builder
    {
        return Project::applyFilters((array) $filters);
    }
}
