<?php

namespace App\Application\Tasks\DTO;

final class CreateTaskDTO
{
    public function __construct(
        public string $project_id,
        public string $title,
        public ?string $description,
        public ?string $due_at,
        public string $user_id
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_at' => $this->due_at,
            'user_id' => $this->user_id,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            project_id: $attributes['project_id'],
            title: $attributes['title'],
            description: $attributes['description'] ?? null,
            due_at: $attributes['due_at'] ?? null,
            user_id: $attributes['user_id'],
        );
    }
}
