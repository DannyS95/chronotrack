<?php

namespace App\Application\Goals\DTO;

class GoalFilterDTO
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $id = null,
        public readonly ?string $goal_date = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?string $deadline = null,
        public readonly ?string $sort_by = null,
        public readonly ?string $order = null,
        public readonly ?int $per_page = 20,
    ) {}

    public function toArray(): array
    {
        return [
            'status'     => $this->status,
            'id'         => $this->id,
            'goal_date'  => $this->goal_date,
            'from'       => $this->from,
            'to'         => $this->to,
            'deadline'   => $this->deadline,
            'sort_by'    => $this->sort_by,
            'order'      => $this->order,
            'per_page'   => $this->per_page,
        ];
    }

    public static function fromArray(array $attributes): self
    {
        return new self(
            status: $attributes['status'] ?? null,
            id: $attributes['id'] ?? null,
            goal_date: $attributes['goal_date'] ?? null,
            from: $attributes['from'] ?? null,
            to: $attributes['to'] ?? null,
            deadline: $attributes['deadline'] ?? null,
            sort_by: $attributes['sort_by'] ?? null,
            order: $attributes['order'] ?? null,
            per_page: $attributes['per_page'] ?? 20,
        );
    }
}
