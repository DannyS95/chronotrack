<?php

namespace App\Application\Projects\DTO;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Str;

readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $deadline,
        public int|string $userId,
        public int | string | null $id, // moved to the end and made optional
    ) {}

    public function toArray(): array
{
    return [
        'id' => $this->id ?? Str::uuid(),
        'name' => $this->name,
        'description' => $this->description,
        'deadline' => $this->deadline,
        'user_id' => $this->userId,
    ];
}


    public static function fromModel(Project $project): self
    {
        return new self(
            id: $project?->id,
            name: $project->name,
            description: $project->description,
            deadline: $project->deadline,
            userId: $project->user_id
        );
    }
}
