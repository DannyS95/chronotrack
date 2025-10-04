<?php

namespace App\Application\Projects\DTO;

final class CompleteProjectDTO
{
    public function __construct(
        public readonly string $projectId,
        public readonly string $userId,
    ) {}
}
