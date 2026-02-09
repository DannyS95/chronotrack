<?php

namespace App\Application\Tasks\DTO;

final class CreateTaskDTO
{
    public function __construct(
        public string $project_id,
        public ?string $goal_id,
        public string $title,
        public ?string $description,
        public ?string $due_at,
        public string $timer_type,
        public ?int $target_duration_seconds,
        public string $user_id
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'goal_id' => $this->goal_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_at' => $this->due_at,
            'timer_type' => $this->timer_type,
            'target_duration_seconds' => $this->target_duration_seconds,
            'user_id' => $this->user_id,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            project_id: $attributes['project_id'],
            goal_id: $attributes['goal_id'] ?? null,
            title: $attributes['title'],
            description: $attributes['description'] ?? null,
            due_at: $attributes['due_at'] ?? null,
            timer_type: $attributes['timer_type'] ?? 'custom',
            target_duration_seconds: isset($attributes['target_duration_seconds'])
                ? (int) $attributes['target_duration_seconds']
                : null,
            user_id: $attributes['user_id'],
        );
    }
}
