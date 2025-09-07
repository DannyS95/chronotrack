<?php

namespace App\Application\Timers\Services;

use App\Application\Timers\DTOs\TimerFilterDTO;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTimersService
{
    public function __construct(
        private readonly TimerRepositoryInterface $repo
    ) {}

    public function handle(TimerFilterDTO $dto): LengthAwarePaginator
    {
        return $this->repo->list($dto);
    }
}
