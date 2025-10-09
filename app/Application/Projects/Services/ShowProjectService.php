<?php

namespace App\Application\Projects\Services;

use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;

final class ShowProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {}

    public function handle(string $projectId, string $userId): Project
    {
        return $this->projectRepository->findOwned($projectId, $userId);
    }
}
