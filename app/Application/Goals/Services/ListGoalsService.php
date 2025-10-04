<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTO\GoalFilterDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Illuminate\Support\Collection;

final class ListGoalsService
{
    public function __construct(
        private readonly GoalRepositoryInterface $goalRepository
    ) {}

    public function handle(GoalFilterDTO $dto, Project $project): Collection
    {
        return $this->goalRepository->list($dto->toArray(), $project);
    }
}
