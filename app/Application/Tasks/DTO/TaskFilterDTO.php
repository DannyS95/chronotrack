<?php

namespace App\Application\Tasks\DTO;

final class TaskFilterDTO
{
        public function __construct(
        public string $project_id,
        public int $user_id,
        public ?string $title = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $sort_by = 'created_at',
        public ?string $order = 'desc',
        public int $per_page = 20,
    ) {}
}
