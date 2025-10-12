<?php

namespace Tests\Feature\Timers;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimerDatabaseInvariantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Timer::query()->getConnection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('Active timer DB invariants require MySQL virtual columns.');
        }
    }

    public function test_prevents_multiple_active_timers_for_same_task(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Concurrency Check',
            'description' => 'Ensure timers serialize on tasks',
            'user_id' => $user->id,
        ]);

        $task = Task::create([
            'project_id' => $project->id,
            'goal_id' => null,
            'title' => 'Primary task',
            'description' => null,
            'due_at' => null,
            'last_activity_at' => null,
            'status' => 'active',
            'time_spent_seconds' => 0,
        ]);

        Timer::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => Carbon::now()->subMinute(),
            'stopped_at' => null,
            'paused_at' => null,
            'paused_total' => 0,
            'duration' => null,
        ]);

        $this->expectException(QueryException::class);

        try {
            Timer::create([
                'task_id' => $task->id,
                'user_id' => $user->id,
                'started_at' => Carbon::now(),
                'stopped_at' => null,
                'paused_at' => null,
                'paused_total' => 0,
                'duration' => null,
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseCount('timers', 1);
            throw $exception;
        }
    }

    public function test_prevents_multiple_active_timers_for_same_user(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'User Scope Project',
            'description' => 'Ensure single active timer per user',
            'user_id' => $user->id,
        ]);

        $firstTask = Task::create([
            'project_id' => $project->id,
            'goal_id' => null,
            'title' => 'First task',
            'description' => null,
            'due_at' => null,
            'last_activity_at' => null,
            'status' => 'active',
            'time_spent_seconds' => 0,
        ]);

        $secondTask = Task::create([
            'project_id' => $project->id,
            'goal_id' => null,
            'title' => 'Second task',
            'description' => null,
            'due_at' => null,
            'last_activity_at' => null,
            'status' => 'active',
            'time_spent_seconds' => 0,
        ]);

        Timer::create([
            'task_id' => $firstTask->id,
            'user_id' => $user->id,
            'started_at' => Carbon::now()->subMinutes(2),
            'stopped_at' => null,
            'paused_at' => null,
            'paused_total' => 0,
            'duration' => null,
        ]);

        $this->expectException(QueryException::class);

        try {
            Timer::create([
                'task_id' => $secondTask->id,
                'user_id' => $user->id,
                'started_at' => Carbon::now(),
                'stopped_at' => null,
                'paused_at' => null,
                'paused_total' => 0,
                'duration' => null,
            ]);
        } catch (QueryException $exception) {
            $this->assertDatabaseCount('timers', 1);
            throw $exception;
        }
    }
}
