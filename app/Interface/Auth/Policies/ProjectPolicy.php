<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;

final class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function complete(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function archive(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    private function ownsProject(User $user, Project $project): bool
    {
        return (string) $project->user_id === (string) $user->getAuthIdentifier();
    }
}
