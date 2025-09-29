<?php

namespace App\Application\Tasks\ViewModels;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;

final class TaskViewModel
{
    public function __construct(private readonly TaskSnapshot $snapshot)
    {
    }

    public static function fromSnapshot(TaskSnapshot $snapshot): self
    {
        return new self($snapshot);
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->snapshot->id,
            'project_id'       => $this->snapshot->projectId,
            'goal_id'          => $this->snapshot->goalId,
            'title'            => $this->snapshot->title,
            'description'      => $this->snapshot->description,
            'status'           => $this->snapshot->status,
            'due_at'           => $this->snapshot->dueAt,
            'last_activity_at' => $this->snapshot->lastActivityAt,
            'created_at'       => $this->snapshot->createdAt,
            'updated_at'       => $this->snapshot->updatedAt,
        ];
    }
}
