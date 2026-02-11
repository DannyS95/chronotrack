<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\Services\UpdateTaskService;
use App\Domain\Common\Contracts\Clock;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Goals\ValueObjects\GoalSnapshot;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Carbon\Carbon;
use Mockery;
use PHPUnit\Framework\TestCase;

final class UpdateTaskServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_stops_active_timer_when_task_marked_done(): void
    {
        $task = new Task();
        $task->id = 'task-1';
        $task->project_id = 'project-1';
        $task->status = 'active';
        $task->created_at = Carbon::now()->subMinute();

        $dto = new UpdateTaskDTO('project-1', 'task-1', 'user-1', ['status' => 'done']);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $goalRepository = Mockery::mock(GoalRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('findOwned')
            ->once()
            ->with('task-1', 'project-1', 'user-1')
            ->andReturn($task);

        $goalRepository->shouldReceive('findOwned')->never();
        $taskRepository->shouldReceive('countIncompleteByGoal')->never();

        $taskRepository->shouldReceive('updateSnapshot')
            ->once()
            ->with($task, Mockery::on(function (array $attributes) {
                return $attributes['status'] === 'done'
                    && isset($attributes['last_activity_at'])
                    && isset($attributes['time_spent_seconds'])
                    && $attributes['time_spent_seconds'] > 0;
            }))
            ->andReturn($this->makeSnapshot('task-1', 'project-1', null, 'done'));

        $timerRepository->shouldReceive('stopActiveTimerForTask')
            ->once()
            ->with('task-1')
            ->andReturn(null);

        $finalSnapshot = $this->makeSnapshot('task-1', 'project-1', null, 'done');

        $taskRepository->shouldReceive('findSnapshot')
            ->once()
            ->with('task-1', 'project-1', 'user-1')
            ->andReturn($finalSnapshot);

        $service = new UpdateTaskService(
            $taskRepository,
            $goalRepository,
            $timerRepository,
            $transactionRunner,
            $this->clock(),
        );

        $result = $service->handle($dto);

        $this->assertSame('done', $result->toArray()['status']);
    }

    public function test_it_does_not_stop_timer_when_status_is_unchanged(): void
    {
        $task = new Task();
        $task->id = 'task-2';
        $task->project_id = 'project-1';
        $task->status = 'active';

        $dto = new UpdateTaskDTO('project-1', 'task-2', 'user-1', ['title' => 'New Title']);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $goalRepository = Mockery::mock(GoalRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('findOwned')
            ->once()
            ->with('task-2', 'project-1', 'user-1')
            ->andReturn($task);

        $goalRepository->shouldReceive('findOwned')->never();
        $taskRepository->shouldReceive('countIncompleteByGoal')->never();

        $updatedSnapshot = $this->makeSnapshot('task-2', 'project-1', null, 'active');

        $taskRepository->shouldReceive('updateSnapshot')
            ->once()
            ->with($task, ['title' => 'New Title'])
            ->andReturn($updatedSnapshot);

        $timerRepository->shouldReceive('stopActiveTimerForTask')->never();
        $taskRepository->shouldReceive('findSnapshot')->never();

        $service = new UpdateTaskService(
            $taskRepository,
            $goalRepository,
            $timerRepository,
            $transactionRunner,
            $this->clock(),
        );

        $result = $service->handle($dto);

        $this->assertSame('active', $result->toArray()['status']);
    }

    public function test_it_completes_goal_when_last_task_marked_done(): void
    {
        $task = new Task();
        $task->id = 'task-3';
        $task->project_id = 'project-1';
        $task->goal_id = 'goal-1';
        $task->status = 'active';
        $task->created_at = Carbon::now()->subMinutes(2);

        $dto = new UpdateTaskDTO('project-1', 'task-3', 'user-1', ['status' => 'done']);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $goalRepository = Mockery::mock(GoalRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('findOwned')
            ->once()
            ->with('task-3', 'project-1', 'user-1')
            ->andReturn($task);

        $goalRepository->shouldReceive('findOwned')->never();

        $taskRepository->shouldReceive('updateSnapshot')
            ->once()
            ->with($task, Mockery::on(function (array $attributes) {
                return $attributes['status'] === 'done'
                    && isset($attributes['time_spent_seconds']);
            }))
            ->andReturn($this->makeSnapshot('task-3', 'project-1', 'goal-1', 'done'));

        $timerRepository->shouldReceive('stopActiveTimerForTask')
            ->once()
            ->with('task-3')
            ->andReturn(null);

        $taskRepository->shouldReceive('countIncompleteByGoal')
            ->once()
            ->with('goal-1', 'project-1', 'user-1')
            ->andReturn(0);

        $goalRepository->shouldReceive('updateStatusSnapshot')
            ->once()
            ->with('goal-1', 'complete', Mockery::type(\DateTimeInterface::class))
            ->andReturn($this->makeGoalSnapshot('goal-1', 'project-1', 'complete'));

        $finalSnapshot = $this->makeSnapshot('task-3', 'project-1', 'goal-1', 'done');

        $taskRepository->shouldReceive('findSnapshot')
            ->once()
            ->with('task-3', 'project-1', 'user-1')
            ->andReturn($finalSnapshot);

        $service = new UpdateTaskService(
            $taskRepository,
            $goalRepository,
            $timerRepository,
            $transactionRunner,
            $this->clock(),
        );

        $result = $service->handle($dto);

        $this->assertSame('done', $result->toArray()['status']);
    }

    private function makeSnapshot(string $taskId, string $projectId, ?string $goalId, string $status): TaskSnapshot
    {
        return new TaskSnapshot(
            id: $taskId,
            projectId: $projectId,
            goalId: $goalId,
            title: 'Sample Task',
            description: null,
            status: $status,
            timerType: 'custom',
            targetDurationSeconds: null,
            targetDurationHuman: null,
            progressPercent: null,
            dueAt: null,
            lastActivityAt: null,
            createdAt: null,
            updatedAt: null,
            activeSince: null,
            accumulatedSeconds: 0,
            accumulatedHuman: null,
            hasActiveTimers: false,
        );
    }

    private function makeGoalSnapshot(string $goalId, string $projectId, string $status): GoalSnapshot
    {
        return new GoalSnapshot(
            id: $goalId,
            projectId: $projectId,
            title: 'Goal',
            status: $status,
            description: null,
            completedAt: null,
        );
    }

    private function clock(): Clock
    {
        return new class implements Clock {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2026-02-09 12:00:00');
            }
        };
    }
}
