<?php

namespace App\Domain\Goals\ValueObjects;

use App\Infrastructure\Goals\Eloquent\Models\Goal as GoalModel;

final class GoalSnapshot
{
    public function __construct(
        public readonly string $id,
        public readonly string $projectId,
        public readonly string $title,
        public readonly ?string $status,
        public readonly ?string $description,
        public readonly ?string $completedAt,
    ) {}

    public static function fromModel(GoalModel $goal): self
    {
        return new self(
            id: $goal->id,
            projectId: $goal->project_id,
            title: $goal->title,
            status: $goal->status,
            description: $goal->description,
            completedAt: $goal->completed_at?->toDateTimeString(),
        );
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
