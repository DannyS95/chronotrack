<?php

namespace App\Application\Projects\Dto;

readonly class ProjectFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $deadlineFrom = null,
        public ?string $deadlineTo = null,
        public ?bool $archived = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDirection = 'desc',
        public int $perPage = 10,
        public int|string $user_id = 0,
    ) {}
}
