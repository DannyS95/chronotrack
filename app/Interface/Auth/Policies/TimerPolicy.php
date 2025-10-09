<?php

namespace App\Interface\Auth\Policies;

use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;

final class TimerPolicy
{
    public function viewAny(User $user, ?Task $task = null): bool
    {
        return $task === null || $this->ownsTask($user, $task);
    }

    public function view(User $user, Timer $timer): bool
    {
        return $this->ownsTimer($user, $timer);
    }

    public function create(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function update(User $user, Timer $timer): bool
    {
        return $this->ownsTimer($user, $timer);
    }

    public function delete(User $user, Timer $timer): bool
    {
        return $this->ownsTimer($user, $timer);
    }

    public function restore(User $user, Timer $timer): bool
    {
        return $this->ownsTimer($user, $timer);
    }

    public function forceDelete(User $user, Timer $timer): bool
    {
        return $this->ownsTimer($user, $timer);
    }

    public function start(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function pause(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
    }

    public function stop(User $user, Task $task): bool
    {
        return $this->ownsTask($user, $task);
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

    private function ownsTimer(User $user, Timer $timer): bool
    {
        if ($timer->user_id !== null) {
            return (string) $timer->user_id === (string) $user->getAuthIdentifier();
        }

        if ($timer->task !== null) {
            return $this->ownsTask($user, $timer->task);
        }

        $task = $timer->task()->with('project')->first();

        return $task !== null && $this->ownsTask($user, $task);
    }
}
