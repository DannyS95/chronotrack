<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\Dto\ProjectFilterDto;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListProjectsService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}

    public function handle(ProjectFilterDto $projectFiltersDto): LengthAwarePaginator
    {
        return $this->repository
            ->getAll($projectFiltersDto->toArray())
            ->paginate($projectFiltersDto->per_page);
    }
}
    