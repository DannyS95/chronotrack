<?php

namespace Tests\Unit\Application\Projects;

use App\Application\Projects\Services\ProjectSummaryService;
use App\Application\Projects\ViewModels\ProjectSummaryViewModel;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ProjectSummaryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_returns_summary_view_model_with_aggregated_stats(): void
    {
        $project = new Project();
        $project->id = 'project-1';
        $project->user_id = 'user-1';
        $project->name = 'Timer Tracker';
        $project->created_at = Carbon::parse('2025-01-01 00:00:00');
        $project->updated_at = Carbon::parse('2025-01-02 00:00:00');

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasks = Mockery::mock(TaskRepositoryInterface::class);
        $goals = Mockery::mock(GoalRepositoryInterface::class);
        $timers = Mockery::mock(TimerRepositoryInterface::class);

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($project);

        $goalComplete = new Goal();
        $goalComplete->id = 'goal-1';
        $goalComplete->project_id = 'project-1';
        $goalComplete->status = 'complete';
        $goalComplete->title = 'Ship MVP';
        $goalComplete->completed_at = Carbon::parse('2025-01-03 00:00:00');

        $goalActive = new Goal();
        $goalActive->id = 'goal-2';
        $goalActive->project_id = 'project-1';
        $goalActive->status = 'active';
        $goalActive->title = 'Collect Feedback';

        $timerCompleted = new Timer();
        $timerCompleted->started_at = Carbon::parse('2025-01-01 00:00:00');
        $timerCompleted->stopped_at = Carbon::parse('2025-01-01 01:00:00');
        $timerCompleted->duration = 3600;
        $timerCompleted->paused_total = 0;

        $timerActive = new Timer();
        $timerActive->started_at = Carbon::parse('2025-01-02 00:00:00');
        $timerActive->stopped_at = null;
        $timerActive->duration = 1800;
        $timerActive->paused_total = 0;

        $taskCompleted = new Task();
        $taskCompleted->id = 'task-1';
        $taskCompleted->project_id = 'project-1';
        $taskCompleted->goal_id = 'goal-1';
        $taskCompleted->title = 'Prepare documentation';
        $taskCompleted->status = 'done';
        $taskCompleted->time_spent_seconds = 3600;
        $taskCompleted->created_at = Carbon::parse('2025-01-01 00:00:00');
        $taskCompleted->updated_at = Carbon::parse('2025-01-01 01:30:00');
        $taskCompleted->setRelation('timers', new Collection([$timerCompleted]));

        $taskActive = new Task();
        $taskActive->id = 'task-2';
        $taskActive->project_id = 'project-1';
        $taskActive->goal_id = null;
        $taskActive->title = 'Polish UI';
        $taskActive->status = 'active';
        $taskActive->time_spent_seconds = 1800;
        $taskActive->created_at = Carbon::parse('2025-01-02 00:00:00');
        $taskActive->updated_at = Carbon::parse('2025-01-02 00:45:00');
        $taskActive->setRelation('timers', new Collection([$timerActive]));

        $tasks->shouldReceive('getTasksByProject')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn(new Collection([$taskCompleted, $taskActive]));

        $goals->shouldReceive('getByProject')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn(new Collection([$goalComplete, $goalActive]));

        $timers->shouldReceive('countActiveByProject')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn(1);

        $service = new ProjectSummaryService($projects, $tasks, $goals, $timers);

        $viewModel = $service->handle('project-1', 'user-1');

        $this->assertInstanceOf(ProjectSummaryViewModel::class, $viewModel);

        $payload = $viewModel->toArray();

        $this->assertSame('project-1', $payload['project']['id']);
        $this->assertSame(2, $payload['tasks']['total']);
        $this->assertSame(1, $payload['tasks']['completed']);
        $this->assertSame(1, $payload['tasks']['active']);
        $this->assertSame(1, $payload['tasks']['active_timers']);
        $this->assertSame(5400, $payload['tasks']['elapsed_seconds']);
        $this->assertSame(5400, $payload['tasks']['time_spent_seconds']);

        $this->assertSame(2, $payload['goals']['total']);
        $this->assertSame(1, $payload['goals']['completed']);
        $this->assertSame(1, $payload['goals']['active']);
        $this->assertSame(0, $payload['goals']['active_timers']);
        $this->assertSame(3600, $payload['goals']['elapsed_seconds']);
        $this->assertSame(3600, $payload['goals']['time_spent_seconds']);

        $this->assertSame(1, $payload['timers']['running']);
        $this->assertTrue($payload['timers']['has_running']);
        $this->assertSame(5400, $payload['timers']['tracked_seconds']);
        $this->assertSame(5400, $payload['timers']['time_spent_seconds']);
    }
}
