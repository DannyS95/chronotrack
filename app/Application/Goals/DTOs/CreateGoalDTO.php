<?php

namespace App\Application\Goals\DTOs;

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
}

