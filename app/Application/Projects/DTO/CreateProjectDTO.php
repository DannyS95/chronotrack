<?php

namespace App\Application\Projects\DTO;

use App\Infrastructure\Projects\Eloquent\Models\Project;

readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $deadline,
        public int|string $user_id,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'deadline' => $this->deadline,
            'user_id' => $this->user_id,
        ];
    }

    public static function fromModel(Project $project): self
    {
        return new self(
            name: $project->name,
            description: $project->description,
            deadline: $project->deadline,
            user_id: $project->user_id
        );
    }
}
