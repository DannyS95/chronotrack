<?php

namespace App\Application\Tasks\DTO;

final class UpdateTaskDTO
{
    public string $project_id;

    public string $task_id;

    public string $user_id;

    /** @var array<string, mixed> */
    public array $attributes;

    public ?string $goal_id;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        string $project_id,
        string $task_id,
        string $user_id,
        array $attributes,
    ) {
        $this->project_id = $project_id;
        $this->task_id = $task_id;
        $this->user_id = $user_id;
        $this->attributes = $attributes;
        $this->goal_id = $attributes['goal_id'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
