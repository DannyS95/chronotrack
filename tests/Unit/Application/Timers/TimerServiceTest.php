<?php

namespace Tests\Unit\Application\Timers;

use App\Application\Timers\Services\TimerService;
use App\Domain\Common\Contracts\TransactionRunner;
use App\Domain\Tasks\Contracts\TaskRepositoryInterface;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use App\Domain\Timers\Exceptions\ActiveTimerExists;
use App\Domain\Timers\Exceptions\NoActiveTimerOnTask;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use PDOException;
use PHPUnit\Framework\TestCase;

final class TimerServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_start_throws_conflict_when_same_task_running(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');
        $existingTimer = new Timer();

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')
            ->once()->with('task-1')->andReturn($existingTimer);
        $timerRepository->shouldReceive('resumeTimer')->never();
        $timerRepository->shouldReceive('findRunningTimersForUser')->never();
        $timerRepository->shouldReceive('pauseActiveTimerForTask')->never();
        $timerRepository->shouldReceive('createRunning')->never();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ActiveTimerExists::class);
        $this->expectExceptionMessage('A timer is already running on this task.');

        $service->start($task, '1');
    }

    public function test_start_resumes_paused_timer_on_same_task(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');
        $pausedTimer = new Timer();
        $pausedTimer->paused_at = Carbon::now()->subMinute();

        $resumedTimer = new Timer();

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')
            ->once()->with('task-1')->andReturn($pausedTimer);
        $timerRepository->shouldReceive('resumeTimer')
            ->once()->with($pausedTimer)->andReturn($resumedTimer);
        $timerRepository->shouldReceive('findRunningTimersForUser')->never();
        $timerRepository->shouldReceive('pauseActiveTimerForTask')->never();
        $timerRepository->shouldReceive('createRunning')->never();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $result = $service->start($task, '1');

        $this->assertSame($resumedTimer, $result);
    }

    public function test_start_pauses_timers_from_other_projects(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');
        $otherTimer = $this->makeTimerWithTask('timer-2', 'task-2', 'project-2', 'goal-9');

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();
        $timerRepository->shouldReceive('findRunningTimersForUser')
            ->once()->with('1', 'task-1')->andReturn(new Collection([$otherTimer]));
        $timerRepository->shouldReceive('pauseActiveTimerForTask')
            ->once()->with('task-2');

        $created = new Timer();
        $timerRepository->shouldReceive('createRunning')
            ->once()->with('task-1', '1')->andReturn($created);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $result = $service->start($task, '1');

        $this->assertSame($created, $result);
    }

    public function test_start_throws_when_timer_running_in_same_goal(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');
        $otherTimer = $this->makeTimerWithTask('timer-2', 'task-2', 'project-1', 'goal-1');

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();
        $timerRepository->shouldReceive('findRunningTimersForUser')
            ->once()->with('1', 'task-1')->andReturn(new Collection([$otherTimer]));
        $timerRepository->shouldReceive('pauseActiveTimerForTask')->never();
        $timerRepository->shouldReceive('createRunning')->never();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ActiveTimerExists::class);
        $this->expectExceptionMessage('A timer is already running for this goal.');

        $service->start($task, '1');
    }

    public function test_start_throws_when_timer_running_in_same_project_different_goal(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');
        $otherTimer = $this->makeTimerWithTask('timer-2', 'task-2', 'project-1', 'goal-2');

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();
        $timerRepository->shouldReceive('findRunningTimersForUser')
            ->once()->with('1', 'task-1')->andReturn(new Collection([$otherTimer]));
        $timerRepository->shouldReceive('pauseActiveTimerForTask')->never();
        $timerRepository->shouldReceive('createRunning')->never();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ActiveTimerExists::class);
        $this->expectExceptionMessage('A timer is already running for this project.');

        $service->start($task, '1');
    }

    public function test_start_throws_when_timer_running_in_same_project_without_goal(): void
    {
        $task = $this->makeTask('task-1', 'project-1', null);
        $otherTimer = $this->makeTimerWithTask('timer-2', 'task-2', 'project-1', null);

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();
        $timerRepository->shouldReceive('findRunningTimersForUser')
            ->once()->with('1', 'task-1')->andReturn(new Collection([$otherTimer]));
        $timerRepository->shouldReceive('pauseActiveTimerForTask')->never();
        $timerRepository->shouldReceive('createRunning')->never();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ActiveTimerExists::class);
        $this->expectExceptionMessage('A timer is already running for this project.');

        $service->start($task, '1');
    }

    public function test_start_fails_when_task_completed(): void
    {
        $task = $this->makeTask('task-closed', 'project-1', null);
        $task->status = 'done';

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldNotReceive('findActiveForTaskLock');
        $timerRepository->shouldNotReceive('findRunningTimersForUser');
        $timerRepository->shouldNotReceive('pauseActiveTimerForTask');
        $timerRepository->shouldNotReceive('createRunning');

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-closed', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ValidationException::class);

        $service->start($task, '1');
    }

    public function test_start_translates_unique_constraint_violation_to_conflict(): void
    {
        $task = $this->makeTask('task-1', 'project-1', 'goal-1');

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('findActiveForTaskLock')->once()->with('task-1')->andReturnNull();
        $timerRepository->shouldReceive('findRunningTimersForUser')
            ->once()->with('1', 'task-1')->andReturn(new Collection());

        $pdoException = new PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'user\' for key \'timers_user_active_unique\'');
        $queryException = new QueryException('insert into timers ...', [], $pdoException);

        $timerRepository->shouldReceive('createRunning')
            ->once()->with('task-1', '1')->andThrow($queryException);

        $conflictingTimer = new Timer();
        $conflictingTimer->id = 'timer-99';
        $conflictingTimer->task_id = 'task-2';

        $conflictingTask = $this->makeTask('task-2', 'project-1', 'goal-1');
        $conflictingTimer->setRelation('task', $conflictingTask);

        $timerRepository->shouldReceive('findActiveTimerForUserLock')
            ->once()->with('1')->andReturn($conflictingTimer);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->shouldReceive('lockOwnedForUpdate')
            ->once()
            ->with('task-1', 'project-1', '1')
            ->andReturn($task);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ActiveTimerExists::class);
        $this->expectExceptionMessage('A timer is already running for this goal.');

        $service->start($task, '1');
    }

    public function test_pause_pauses_active_timer(): void
    {
        $task = $this->makeTask('task-1', 'project-1', null);
        $task->status = 'active';

        $pausedTimer = new Timer();
        $pausedTimer->paused_at = Carbon::now();

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('pauseActiveTimerForTask')
            ->once()->with('task-1')->andReturn($pausedTimer);

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $result = $service->pause($task, '1');

        $this->assertSame($pausedTimer, $result);
    }

    public function test_pause_fails_when_no_active_timer(): void
    {
        $task = $this->makeTask('task-1', 'project-1', null);
        $task->status = 'active';

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldReceive('pauseActiveTimerForTask')
            ->once()->with('task-1')->andReturnNull();

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(NoActiveTimerOnTask::class);

        $service->pause($task, '1');
    }

    public function test_pause_fails_when_task_completed(): void
    {
        $task = $this->makeTask('task-1', 'project-1', null);
        $task->status = 'done';

        $timerRepository = Mockery::mock(TimerRepositoryInterface::class);
        $timerRepository->shouldNotReceive('pauseActiveTimerForTask');

        $taskRepository = Mockery::mock(TaskRepositoryInterface::class);

        $service = new TimerService($timerRepository, $this->transactionRunner(), $taskRepository);

        $this->expectException(ValidationException::class);

        $service->pause($task, '1');
    }

    private function transactionRunner(): TransactionRunner
    {
        return new class implements TransactionRunner {
            public function run(callable $callback)
            {
                return $callback();
            }
        };
    }

    private function makeTask(string $taskId, string $projectId, ?string $goalId): Task
    {
        $task = new Task();
        $task->id = $taskId;
        $task->project_id = $projectId;
        $task->goal_id = $goalId;
        $task->status = 'active';

        return $task;
    }

    private function makeTimerWithTask(string $timerId, string $taskId, string $projectId, ?string $goalId): Timer
    {
        $timer = new Timer();
        $timer->id = $timerId;
        $timer->task_id = $taskId;

        $task = $this->makeTask($taskId, $projectId, $goalId);
        $timer->setRelation('task', $task);

        return $timer;
    }
}
