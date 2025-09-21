<?php

namespace App\Application\Goals\DTO;

class GoalFilterDTO
{
    public function __construct(
        public readonly ?string $status = null,
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
            'from'       => $this->from,
            'to'         => $this->to,
            'deadline'   => $this->deadline,
            'sort_by'    => $this->sort_by,
            'order'      => $this->order,
            'per_page'   => $this->per_page,
        ];
    }
}
