<?php

namespace App\Application\Timers\ViewModels;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class TimerCollectionViewModel
{
    /**
     * @return array{
     *     data: array<int, array<string, mixed>>,
     *     meta: array<string, int|string|null>
     * }
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): array
    {
        $items = self::mapTimers($paginator);

        return [
            'data' => $items->all(),
            'meta' => [
                'total' => $paginator->total(),
                'count' => $items->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private static function mapTimers(LengthAwarePaginator $paginator): Collection
    {
        return collect($paginator->items())
            ->map(fn ($timer) => TimerViewModel::fromModel($timer)->toArray())
            ->values();
    }
}
