<?php

namespace App\Application\Goals\DTO;

class AttachTaskToGoalDTO
{
    public function __construct(
        public string $projectId,
        public string $goalId,
        public string $taskId,
        public string $userId,
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->projectId,
            'goal_id'    => $this->goalId,
            'task_id'    => $this->taskId,
            'user_id'    => $this->userId,
        ];
    }
}
