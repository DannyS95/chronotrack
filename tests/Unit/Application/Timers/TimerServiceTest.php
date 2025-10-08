<?php

namespace Tests\Unit\Application\Timers;

use App\Application\Timers\Services\TimerService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\ActiveTimerWithinGoalException;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Validation\ValidationException;
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

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();

        $activeTimer = new Timer();
        $activeTimer->id = 'active-timer';

        $timerRepository->shouldReceive('findActiveForGoalLock')
            ->once()
            ->with('goal-1', '1', 'task-1')
            ->andReturn($activeTimer);

        $service = new TimerService($timerRepository, $transactionRunner);

        $this->expectException(ActiveTimerWithinGoalException::class);

        $service->start($task, '1');
    }

    public function test_start_fails_when_unscoped_task_already_has_active_timer(): void
    {
        $task = new Task();
        $task->id = 'task-2';
        $task->goal_id = null;

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-2')->andReturnNull();
        $timerRepository->shouldReceive('findActiveForGoalLock')->never();

        $activeTimer = new Timer();
        $activeTimer->id = 'active-unscoped';

        $timerRepository->shouldReceive('findActiveWithoutGoalForUserLock')
            ->once()
            ->with('1', 'task-2')
            ->andReturn($activeTimer);

        $service = new TimerService($timerRepository, $transactionRunner);

        $this->expectException(ActiveTimerExists::class);

        $service->start($task, '1');
    }

    public function test_start_creates_timer_when_no_conflicts_present(): void
    {
        $task = new Task();
        $task->id = 'task-3';
        $task->goal_id = 'goal-3';

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-3')->andReturnNull();
        $timerRepository->shouldReceive('findActiveForGoalLock')->once()->with('goal-3', '1', 'task-3')->andReturnNull();
        $timerRepository->shouldReceive('findActiveWithoutGoalForUserLock')->never();

        $createdTimer = new Timer();
        $createdTimer->id = 'new-timer';

        $timerRepository->shouldReceive('createRunning')
            ->once()
            ->with('task-3', '1')
            ->andReturn($createdTimer);

        $service = new TimerService($timerRepository, $transactionRunner);

        $result = $service->start($task, '1');

        $this->assertSame($createdTimer, $result);
    }

    public function test_start_fails_when_task_already_completed(): void
    {
        $task = new Task();
        $task->id = 'task-4';
        $task->status = 'done';

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldNotReceive('findActiveForTaskLock');

        $transactionRunner = new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };

        $service = new TimerService($timerRepository, $transactionRunner);

        $this->expectException(ValidationException::class);

        $service->start($task, '1');
    }
}
