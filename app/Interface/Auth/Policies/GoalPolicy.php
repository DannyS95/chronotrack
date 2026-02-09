<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Workspaces\Eloquent\Models\Workspace;

final class GoalPolicy
{
    public function viewAny(User $user, Project|Workspace $workspace): bool
    {
        return $this->ownsWorkspace($user, $workspace);
    }

    public function view(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    public function create(User $user, Project|Workspace $workspace): bool
    {
        return $this->ownsWorkspace($user, $workspace);
    }

    public function update(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    public function delete(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    public function complete(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    public function attachTask(User $user, Goal $goal, Project|Workspace $workspace): bool
    {
        return $goal->project_id === $workspace->id && $this->ownsWorkspace($user, $workspace);
    }

    public function detachTask(User $user, Goal $goal, Project|Workspace $workspace): bool
    {
        return $goal->project_id === $workspace->id && $this->ownsWorkspace($user, $workspace);
    }

    public function progress(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    private function ownsWorkspace(User $user, Project|Workspace $workspace): bool
    {
        return (string) $workspace->user_id === (string) $user->getAuthIdentifier();
    }

    private function ownsGoal(User $user, Goal $goal): bool
    {
        $project = $goal->project;

        if ($project === null) {
            $project = $goal->project()->first();
        }

        return $project !== null && $this->ownsWorkspace($user, $project);
    }
}
