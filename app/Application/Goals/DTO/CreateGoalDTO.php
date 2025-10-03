<?php

namespace App\Application\Goals\DTO;

class CreateGoalDTO
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $deadline,
        public readonly string $status = 'active',
        public readonly string $project_id, // always required now
        public readonly string $user_id,
    ) {}

    public function toArray(): array
    {
        return [
            'title'       => $this->title,
            'description' => $this->description,
            'deadline'    => $this->deadline,
            'status'      => $this->status,
            'project_id'  => $this->project_id,
            'user_id'     => $this->user_id,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            title: $attributes['title'],
            description: $attributes['description'] ?? null,
            deadline: $attributes['deadline'] ?? null,
            status: $attributes['status'] ?? 'active',
            project_id: $attributes['project_id'],
            user_id: $attributes['user_id'],
        );
    }
}
