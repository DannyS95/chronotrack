<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTOs\CreateGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;

final class CreateGoalService
{
    public function __construct(
        private readonly GoalRepositoryInterface $repo
    ) {}

    public function handle(CreateGoalDTO $dto)
    {
        return $this->repo->create($dto->toArray());
    }
}
