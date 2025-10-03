<?php

namespace App\Application\Tasks\DTO;

final class DeleteTaskDTO
{
    public function __construct(
        public string $project_id,
        public string $task_id,
        public string $user_id,
    ) {}
}
