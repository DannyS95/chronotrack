<?php

namespace Tests\Unit\Application\Projects;

use App\Application\Projects\Services\ShowProjectService;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ShowProjectServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_returns_owned_project(): void
    {
        $project = new Project();
        $project->id = 'project-1';
        $project->user_id = 'user-1';

        $repository = Mockery::mock(ProjectRepositoryInterface::class);
        $repository->shouldReceive('findOwned')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($project);

        $service = new ShowProjectService($repository);

        $result = $service->handle('project-1', 'user-1');

        $this->assertSame($project, $result);
    }
}
