<?php

namespace Tests\Unit\Application\Projects;

use App\Application\Projects\Services\ProjectLifecycleService;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Projects\Enums\ProjectCompletionSource;
use App\Domain\Projects\Enums\ProjectStatus;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ProjectLifecycleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_marks_project_complete_when_work_is_finished(): void
    {
        $project = new Project([
            'id' => 'project-1',
            'user_id' => 'user-1',
            'status' => ProjectStatus::Active->value,
            'completion_source' => null,
        ]);

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasks = Mockery::mock(TaskRepositoryInterface::class);
        $goals = Mockery::mock(GoalRepositoryInterface::class);

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($project);

        $tasks->shouldReceive('countByProject')->once()->with('project-1', 'user-1')->andReturn(3);
        $tasks->shouldReceive('countIncompleteByProject')->once()->with('project-1', 'user-1')->andReturn(0);

        $goals->shouldReceive('countByProject')->once()->with('project-1', 'user-1')->andReturn(1);
        $goals->shouldReceive('countIncompleteByProject')->once()->with('project-1', 'user-1')->andReturn(0);

        $projects->shouldReceive('markComplete')
            ->once()
            ->with($project, ProjectCompletionSource::Automatic->value)
            ->andReturn($project);

        $service = new ProjectLifecycleService($projects, $tasks, $goals);

        $service->refresh('project-1', 'user-1');

        $this->addToAssertionCount(1);
    }

    public function test_it_marks_project_active_when_incomplete_work_exists(): void
    {
        $project = new Project([
            'id' => 'project-2',
            'user_id' => 'user-1',
            'status' => ProjectStatus::Complete->value,
            'completion_source' => ProjectCompletionSource::Automatic->value,
        ]);

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasks = Mockery::mock(TaskRepositoryInterface::class);
        $goals = Mockery::mock(GoalRepositoryInterface::class);

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-2', 'user-1')
            ->andReturn($project);

        $tasks->shouldReceive('countByProject')->once()->with('project-2', 'user-1')->andReturn(1);
        $tasks->shouldReceive('countIncompleteByProject')->once()->with('project-2', 'user-1')->andReturn(1);

        $goals->shouldReceive('countByProject')->once()->with('project-2', 'user-1')->andReturn(0);
        $goals->shouldReceive('countIncompleteByProject')->once()->with('project-2', 'user-1')->andReturn(0);

        $projects->shouldReceive('markComplete')->never();
        $projects->shouldReceive('markActive')->once()->with($project)->andReturn($project);

        $service = new ProjectLifecycleService($projects, $tasks, $goals);

        $service->refresh('project-2', 'user-1');

        $this->addToAssertionCount(1);
    }

    public function test_it_does_not_change_manually_completed_projects(): void
    {
        $project = new Project([
            'id' => 'project-3',
            'user_id' => 'user-1',
            'status' => ProjectStatus::Complete->value,
            'completion_source' => ProjectCompletionSource::Manual->value,
        ]);

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasks = Mockery::mock(TaskRepositoryInterface::class);
        $goals = Mockery::mock(GoalRepositoryInterface::class);

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-3', 'user-1')
            ->andReturn($project);

        $tasks->shouldReceive('countByProject')->never();
        $tasks->shouldReceive('countIncompleteByProject')->never();
        $goals->shouldReceive('countByProject')->never();
        $goals->shouldReceive('countIncompleteByProject')->never();

        $projects->shouldReceive('markComplete')->never();
        $projects->shouldReceive('markActive')->never();

        $service = new ProjectLifecycleService($projects, $tasks, $goals);

        $service->refresh('project-3', 'user-1');

        $this->addToAssertionCount(1);
    }

    public function test_it_marks_project_manual_complete(): void
    {
        $project = new Project([
            'id' => 'project-4',
            'user_id' => 'user-1',
            'status' => ProjectStatus::Active->value,
            'completion_source' => null,
        ]);

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasks = Mockery::mock(TaskRepositoryInterface::class);
        $goals = Mockery::mock(GoalRepositoryInterface::class);

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-4', 'user-1')
            ->andReturn($project);

        $projects->shouldReceive('markComplete')
            ->once()
            ->with($project, ProjectCompletionSource::Manual->value)
            ->andReturn($project);

        $service = new ProjectLifecycleService($projects, $tasks, $goals);

        $result = $service->completeManually('project-4', 'user-1');

        $this->assertInstanceOf(Project::class, $result);
    }
}
