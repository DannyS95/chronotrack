<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\DTO\CreateProjectDTO;
use App\Application\Projects\Dto\ProjectFilterDto;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ListProjectsService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}

    public function handle(ProjectFilterDto $projectFiltersDto): LengthAwarePaginator
    {
        return $this->repository
            ->getAll($projectFiltersDto)
            ->paginate($projectFiltersDto->perPage);
    }
}
