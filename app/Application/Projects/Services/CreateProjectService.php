<?php

namespace App\Application\Projects\Services;

use App\Domain\Projects\Contracts\ProjectRepositoryInterface;

final class CreateProjectService
{
    public function __construct(private ProjectRepositoryInterface $repository) {}

    public function handle(array $data)
    {
        return $this->repository->create($data);
    }
}

