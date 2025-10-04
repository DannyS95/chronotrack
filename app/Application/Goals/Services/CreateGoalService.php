<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\CreateGoalDTO;
use App\Application\Projects\Services\ProjectLifecycleService;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;

final class CreateGoalService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository,
        private readonly ProjectLifecycleService $projectLifecycleService,
    ) {}

    public function handle(CreateGoalDTO $dto)
    {
        $goal = $this->goalRepository->create($dto->toArray());

        $this->projectLifecycleService->refresh($dto->project_id, $dto->user_id);

        return $goal;
    }
}
