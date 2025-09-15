<?php

namespace App\Application\Goals\DTOs;

class GoalFilterDTO
{
    public function __construct(
        public readonly string $project_id,
        public readonly string $user_id,
        public readonly ?string $status = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?string $sort_by = null,
        public readonly ?string $order = null,
        public readonly ?int $per_page = 20,
    ) {}

    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'user_id'    => $this->user_id,
            'status'     => $this->status,
            'from'       => $this->from,
            'to'         => $this->to,
            'sort_by'    => $this->sort_by,
            'order'      => $this->order,
            'per_page'   => $this->per_page,
        ];
    }
}
