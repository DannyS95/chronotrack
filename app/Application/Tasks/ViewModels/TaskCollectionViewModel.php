<?php

namespace App\Application\Tasks\ViewModels;

use App\Domain\Tasks\ValueObjects\TaskSnapshot;
use Illuminate\Pagination\LengthAwarePaginator;

final class TaskCollectionViewModel
{
    /**
     * @param array<int, TaskViewModel> $items
     */
    private function __construct(
        private readonly array $items,
        private readonly array $meta,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        $items = $paginator->getCollection()
            ->map(fn(TaskSnapshot $snapshot) => TaskViewModel::fromSnapshot($snapshot))
            ->values()
            ->all();

        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
        ];

        return new self($items, $meta);
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn(TaskViewModel $item) => $item->toArray(), $this->items),
            'meta' => $this->meta,
        ];
    }
}
