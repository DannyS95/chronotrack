<?php

namespace App\Application\Goals\Services;

use App\Application\Goals\DTOs\GoalFilterDTO;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use Illuminate\Support\Collection;

final class ListGoalsService
{
    public function __construct(
        private readonly GoalRepositoryInterface $repo
    ) {}

    public function handle(GoalFilterDTO $dto): Collection
    {
        return $this->repo->list($dto->toArray());
    }
}
