<?php

namespace Tests\Unit\Application\Timers;

use App\Application\Timers\Services\TimerService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\ActiveTimerWithinGoalException;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Mockery;
use PHPUnit\Framework\TestCase;

final class TimerServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_start_fails_when_goal_has_active_timer(): void
    {
        $task = new Task();
        $task->id = 'task-1';
        $task->goal_id = 'goal-1';

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('userOwnsTask')->once()->with('task-1', 'user-1')->andReturn(true);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();

        $activeTimer = new Timer();
        $activeTimer->id = 'active-timer';

        $timerRepository->shouldReceive('findActiveForGoalLock')
            ->once()
            ->with('goal-1', 'user-1', 'task-1')
            ->andReturn($activeTimer);

        $service = new TimerService($timerRepository, $taskRepository, $transactionRunner);

        $this->expectException(ActiveTimerWithinGoalException::class);

        $service->start($task, 'user-1');
    }

    public function test_start_fails_when_unscoped_task_already_has_active_timer(): void
    {
        $task = new Task();
        $task->id = 'task-2';
        $task->goal_id = null;

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('userOwnsTask')->once()->with('task-2', 'user-1')->andReturn(true);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-2')->andReturnNull();
        $timerRepository->shouldReceive('findActiveForGoalLock')->never();

        $activeTimer = new Timer();
        $activeTimer->id = 'active-unscoped';

        $timerRepository->shouldReceive('findActiveWithoutGoalForUserLock')
            ->once()
            ->with('user-1', 'task-2')
            ->andReturn($activeTimer);

        $service = new TimerService($timerRepository, $taskRepository, $transactionRunner);

        $this->expectException(ActiveTimerExists::class);

        $service->start($task, 'user-1');
    }

    public function test_start_creates_timer_when_no_conflicts_present(): void
    {
        $task = new Task();
        $task->id = 'task-3';
        $task->goal_id = 'goal-3';

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $taskRepository->shouldReceive('userOwnsTask')->once()->with('task-3', 'user-1')->andReturn(true);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-3')->andReturnNull();
        $timerRepository->shouldReceive('findActiveForGoalLock')->once()->with('goal-3', 'user-1', 'task-3')->andReturnNull();
        $timerRepository->shouldReceive('findActiveWithoutGoalForUserLock')->never();

        $createdTimer = new Timer();
        $createdTimer->id = 'new-timer';

        $timerRepository->shouldReceive('createRunning')
            ->once()
            ->with('task-3')
            ->andReturn($createdTimer);

        $service = new TimerService($timerRepository, $taskRepository, $transactionRunner);

        $result = $service->start($task, 'user-1');

        $this->assertSame($createdTimer, $result);
    }
}
