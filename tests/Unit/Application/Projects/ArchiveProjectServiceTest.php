<?php

namespace Tests\Unit\Application\Projects;

use App\Application\Projects\DTO\ArchiveProjectDTO;
use App\Application\Projects\Services\ArchiveProjectService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Projects\Contracts\ProjectRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

final class ArchiveProjectServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_archives_project_and_cascades(): void
    {
        $project = new Project();
        $project->id = 'project-1';
        $project->user_id = 'user-1';

        $taskA = new Task();
        $taskA->id = 'task-a';
        $taskB = new Task();
        $taskB->id = 'task-b';
        $tasks = new Collection([$taskA, $taskB]);

        $goal = new Goal();
        $goal->id = 'goal-1';
        $goal->status = 'active';
        $goal->project_id = 'project-1';
        $goal->title = 'Launch';
        $goal->description = null;
        $goal->completed_at = null;
        $goals = new Collection([$goal]);

        $projects = Mockery::mock(ProjectRepositoryInterface::class);
        $tasksRepo = Mockery::mock(TaskRepositoryInterface::class);
        $goalsRepo = Mockery::mock(GoalRepositoryInterface::class);
        $timersRepo = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $projects->shouldReceive('findOwned')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($project);

        $tasksRepo->shouldReceive('getTasksByProject')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($tasks);

        $timersRepo->shouldReceive('stopActiveTimersForTasks')
            ->once()
            ->with(['task-a', 'task-b'])
            ->andReturn(2);

        $tasksRepo->shouldReceive('markTasksAsComplete')
            ->once()
            ->with(['task-a', 'task-b'])
            ->andReturn(2);

        $timersRepo->shouldReceive('deleteTimersForTasks')
            ->once()
            ->with(['task-a', 'task-b'])
            ->andReturn(2);

        $tasksRepo->shouldReceive('delete')
            ->twice()
            ->with(Mockery::on(fn(Task $task) => in_array($task->id, ['task-a', 'task-b'], true)));

        $goalsRepo->shouldReceive('getByProject')
            ->once()
            ->with('project-1', 'user-1')
            ->andReturn($goals);

        $goalsRepo->shouldReceive('updateStatusSnapshot')
            ->once()
            ->with('goal-1', 'complete', Mockery::on(fn($value) => is_string($value) || $value instanceof Carbon))
            ->andReturn(GoalSnapshot::fromModel($goal));

        $goalsRepo->shouldReceive('delete')
            ->once()
            ->with($goal);

        $projects->shouldReceive('delete')
            ->once()
            ->with($project);

        $service = new ArchiveProjectService(
            $projects,
            $tasksRepo,
            $goalsRepo,
            $timersRepo,
            $transactionRunner,
        );

        $result = $service->handle(new ArchiveProjectDTO('project-1', 'user-1'));

        $this->assertSame(
            [
                'project_id' => 'project-1',
                'tasks_archived' => 2,
                'goals_archived' => 1,
                'timers_stopped' => 2,
            ],
            $result,
        );
    }
}
