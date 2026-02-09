<?php

namespace Database\Factories;

use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Timers\Eloquent\Models\Timer>
 */
class TimerFactory extends Factory
{
    protected $model = Timer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = Carbon::now()->subMinutes($this->faker->numberBetween(5, 60));

        return [
            'task_id' => Task::factory(),
            'user_id' => null,
            'started_at' => $startedAt,
            'paused_at' => null,
            'paused_total' => 0,
            'stopped_at' => null,
            'duration' => null,
        ];
    }
}
