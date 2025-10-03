<?php

namespace App\Application\Goals\DTO;

final class CompleteGoalDTO
{
    public function __construct(
        public string $projectId,
        public string $goalId,
        public string $userId,
    ) {}
}
