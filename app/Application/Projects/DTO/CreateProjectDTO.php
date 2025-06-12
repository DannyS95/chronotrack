<?php

namespace App\Application\Projects\DTO;

readonly class CreateProjectDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $deadline,
        public int|string $userId,
    ) {}
}
