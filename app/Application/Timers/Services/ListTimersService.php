<?php

namespace App\Application\Timers\Services;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Application\Timers\ViewModels\TimerViewModel;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTimersService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timerRepository
    ) {}

    public function handle(TimerFilterDTO $dto): LengthAwarePaginator
    {
        return $this->timerRepository
            ->list($dto->toArray())
            ->through(fn($timer) => TimerViewModel::fromModel($timer)->toArray());
    }
}
