<?php

namespace App\Infrastructure\Projects\Persistence\Eloquent;

use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Enums\ProjectCompletionSource;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

final class ProjectRepository implements ProjectRepositoryInterface
{
    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function findById(string $id): ?Project
    {
        return Project::with('users')->find($id);
    }

    public function getAllByUserId(string $id): Collection
    {
        return Project::where('user_id', $id)->latest()->get();
    }

    public function getAll(array $filters): Builder
    {
        return Project::applyFilters((array) $filters);
    }

    public function findOwned(string $projectId, string $userId): Project
    {
        return Project::query()
            ->where('id', $projectId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function markComplete(Project $project, string $source): Project
    {
        if ($source === ProjectCompletionSource::Automatic->value
            && $project->completion_source === ProjectCompletionSource::Manual->value) {
            return $project;
        }

        if (
            $project->status === ProjectStatus::Complete->value
            && $project->completion_source === $source
            && $project->completed_at !== null
        ) {
            return $project;
        }

        $project->status = ProjectStatus::Complete->value;
        $project->completion_source = $source;
        $project->completed_at = Carbon::now();
        $project->save();

        return $project->refresh();
    }

    public function markActive(Project $project): Project
    {
        if ($project->completion_source === ProjectCompletionSource::Manual->value) {
            return $project;
        }

        if ($project->status === ProjectStatus::Active->value && $project->completion_source === null) {
            return $project;
        }

        $project->status = ProjectStatus::Active->value;
        $project->completion_source = null;
        $project->completed_at = null;
        $project->save();

        return $project->refresh();
    }
}
