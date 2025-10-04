<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\CreateGoalDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;

final class CreateGoalService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository
    ) {}

    public function handle(CreateGoalDTO $dto)
    {
        return $this->goalRepository->create($dto->toArray());
    }
}
