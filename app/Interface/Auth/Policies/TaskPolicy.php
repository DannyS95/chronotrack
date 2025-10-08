<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;

final class TaskPolicy
{
    public function viewAny(User $user, ?Project $project = null): bool
    {
        return $project === null || $this->ownsProject($user, $project);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->ownsProject($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    private function ownsProject(User $user, Project $project): bool
    {
        return (string) $project->user_id === (string) $user->getAuthIdentifier();
    }

    private function ownsTask(User $user, Task $task): bool
    {
        $project = $task->project;

        if ($project === null) {
            $ownerId = $task->project()->value('user_id');
            return $ownerId !== null && (string) $ownerId === (string) $user->getAuthIdentifier();
        }

        return (string) $project->user_id === (string) $user->getAuthIdentifier();
    }
}
