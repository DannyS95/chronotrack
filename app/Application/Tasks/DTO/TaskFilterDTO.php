<?php

namespace App\Application\Tasks\DTO;

final class TaskFilterDTO
{
    public function __construct(
        public string $project_id,
        public string $user_id,
        public ?string $goal_id = null,
        public ?string $title = null,
        public ?string $status = null,
        public ?string $timer_type = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $sort_by = 'created_at',
        public ?string $order = 'desc',
        public int $per_page = 20,
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'goal_id' => $this->goal_id,
            'title' => $this->title,
            'status' => $this->status,
            'timer_type' => $this->timer_type,
            'from' => $this->from,
            'to' => $this->to,
            'sort_by' => $this->sort_by,
            'order' => $this->order,
            'per_page' => $this->per_page,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            project_id: $attributes['project_id'],
            user_id: $attributes['user_id'],
            goal_id: $attributes['goal_id'] ?? null,
            title: $attributes['title'] ?? null,
            status: $attributes['status'] ?? null,
            timer_type: $attributes['timer_type'] ?? null,
            from: $attributes['from'] ?? null,
            to: $attributes['to'] ?? null,
            sort_by: $attributes['sort_by'] ?? 'created_at',
            order: $attributes['order'] ?? 'desc',
            per_page: $attributes['per_page'] ?? 20,
        );
    }
}
