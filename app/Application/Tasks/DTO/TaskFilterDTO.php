<?php

namespace App\Application\Tasks\DTO;

final class TaskFilterDTO
{
    public function __construct(
        public string $project_id,
        public string $user_id,
        public ?string $title = null,
        public ?string $status = null,
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
            'title' => $this->title,
            'status' => $this->status,
            'from' => $this->from,
            'to' => $this->to,
            'sort_by' => $this->sort_by,
            'order' => $this->order,
            'per_page' => $this->per_page,
        ];
    }
}
