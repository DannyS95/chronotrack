<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Workspaces\Eloquent\Models\Workspace;

final class TaskPolicy
{
    public function viewAny(User $user, Project|Workspace|null $workspace = null): bool
    {
        return $workspace === null || $this->ownsWorkspace($user, $workspace);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function create(User $user, Project|Workspace $workspace): bool
    {
        return $this->ownsWorkspace($user, $workspace);
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

    private function ownsWorkspace(User $user, Project|Workspace $workspace): bool
    {
        return (string) $workspace->user_id === (string) $user->getAuthIdentifier();
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
