<?php

namespace App\Domain\Tasks\ValueObjects;

use App\Infrastructure\Tasks\Eloquent\Models\Task as TaskModel;

final class TaskSnapshot
{
    public function __construct(
        public readonly string $id,
        public readonly string $projectId,
        public readonly ?string $goalId,
        public readonly string $title,
        public readonly ?string $status,
    ) {}

    public static function fromModel(TaskModel $task): self
    {
        return new self(
            id: $task->id,
            projectId: $task->project_id,
            goalId: $task->goal_id,
            title: $task->title,
            status: $task->status,
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'done';
    }
}
