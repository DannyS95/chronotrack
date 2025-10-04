<?php

namespace Tests\Unit\Application\Tasks;

use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\Services\UpdateTaskService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Goals\Contracts\GoalRepositoryInterface;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
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

        $taskRepository->shouldReceive('updateSnapshot')
            ->once()
            ->with($task, ['status' => 'done'])
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
        );

        $result = $service->handle($dto);

        $this->assertSame('active', $result->toArray()['status']);
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
            dueAt: null,
            lastActivityAt: null,
            createdAt: null,
            updatedAt: null,
            activeSince: null,
            activeDurationSeconds: 0,
            activeDurationHuman: null,
        );
    }
}
