<?php

namespace Database\Factories;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Infrastructure\Projects\Eloquent\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->sentence(),
            'deadline' => null,
            'user_id' => User::factory(),
            'status' => 'active',
            'completed_at' => null,
            'completion_source' => null,
        ];
    }
}
