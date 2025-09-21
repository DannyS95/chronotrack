<?php

namespace App\Application\Tasks\DTO;

final class CreateTaskDTO
{
    public function __construct(
        public string $project_id,
        public string $title,
        public ?string $description,
        public ?string $due_at,
        public int $user_id
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
}
