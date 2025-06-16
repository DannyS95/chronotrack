<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\DTO\CreateProjectDTO;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use Illuminate\Support\Collection;

class ListProjectsService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}

    public function handle(int|string $userId): Collection
    {
        return $this->repository->getAllByUserId($userId)
         ->map(fn($project) => CreateProjectDTO::fromModel($project));
    }
}
