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
}
