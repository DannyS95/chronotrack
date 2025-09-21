<?php

namespace App\Application\Projects\Dto;

readonly class ProjectFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $deadlineFrom = null,
        public ?string $deadlineTo = null,
        public ?bool $archived = null,
        public ?string $sort_by = 'created_at',
        public ?string $sortDirection = 'desc',
        public int $per_page = 10,
        public int|string $user_id = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'search'        => $this->search,
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'from'          => $this->from,
            'to'            => $this->to,
            'deadline_from' => $this->deadlineFrom,
            'deadline_to'   => $this->deadlineTo,
            'archived'      => $this->archived,
            'sort_by'       => $this->sort_by,
            'sort_direction'=> $this->sortDirection,
            'user_id'       => $this->user_id,
        ];
    }
}
