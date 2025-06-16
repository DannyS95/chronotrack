<?php

namespace App\Application\Projects\Dto;

readonly class ProjectFilterDto
{
    public function __construct(
        public ?string $search = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDirection = 'desc',
        public int $perPage = 10
    ) {}
}
