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
        public readonly ?string $description,
        public readonly ?string $status,
        public readonly ?string $dueAt,
        public readonly ?string $lastActivityAt,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function fromModel(TaskModel $task): self
    {
        $formatDate = static fn($value) => match (true) {
            $value instanceof \DateTimeInterface => $value->toDateTimeString(),
            is_string($value)                    => $value,
            default                              => null,
        };

        return new self(
            id: $task->id,
            projectId: $task->project_id,
            goalId: $task->goal_id,
            title: $task->title,
            description: $task->description,
            status: $task->status,
            dueAt: $formatDate($task->due_at),
            lastActivityAt: $formatDate($task->last_activity_at),
            createdAt: $formatDate($task->created_at),
            updatedAt: $formatDate($task->updated_at),
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'done';
    }
}
