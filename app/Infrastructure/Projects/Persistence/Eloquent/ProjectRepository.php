<?php

namespace App\Infrastructure\Projects\Persistence\Eloquent;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
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
    public function getAllByUserId(string $id): Collection
    {
        return Project::where('user_id', $id)->latest()->get();
    }
}
