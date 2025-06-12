<?php

namespace App\Application\Projects\Services;

use App\Application\Projects\Dto\CreateProjectDTO;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;

final class CreateProjectService
{
    public function __construct(private ProjectRepositoryInterface $repository) {}

    public function handle(CreateProjectDTO $createProjectDto)
    {
        return $this->repository->create((array) $createProjectDto);
    }
}

