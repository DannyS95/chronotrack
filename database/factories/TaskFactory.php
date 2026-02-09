<?php

namespace Database\Factories;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Tasks\Eloquent\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'project_id' => Project::factory(),
            'goal_id' => null,
            'title' => $this->faker->sentence,
            'description' => $this->faker->optional()->sentence(),
            'due_at' => null,
            'last_activity_at' => null,
            'status' => 'active',
            'timer_type' => 'custom',
            'target_duration_seconds' => 1800,
            'time_spent_seconds' => 0,
        ];
    }
}
