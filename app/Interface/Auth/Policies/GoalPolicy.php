<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;

final class GoalPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function view(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
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

    public function attachTask(User $user, Goal $goal, Project $project): bool
    {
        return $goal->project_id === $project->id && $this->ownsProject($user, $project);
    }

    public function detachTask(User $user, Goal $goal, Project $project): bool
    {
        return $goal->project_id === $project->id && $this->ownsProject($user, $project);
    }

    public function progress(User $user, Goal $goal): bool
    {
        return $this->ownsGoal($user, $goal);
    }

    private function ownsProject(User $user, Project $project): bool
    {
        return (string) $project->user_id === (string) $user->getAuthIdentifier();
    }

    private function ownsGoal(User $user, Goal $goal): bool
    {
        $project = $goal->project;

        if ($project === null) {
            $project = $goal->project()->first();
        }

        return $project !== null && $this->ownsProject($user, $project);
    }
}
