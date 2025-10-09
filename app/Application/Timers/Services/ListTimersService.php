<?php

namespace App\Application\Timers\Services;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Application\Timers\ViewModels\TimerCollectionViewModel;
use App\Domain\Timers\Contracts\TimerRepositoryInterface;

final class ListTimersService
{
    public function __construct(
        private readonly TimerRepositoryInterface $timerRepository
    ) {}

    public function handle(TimerFilterDTO $dto): array
    {
        $paginator = $this->timerRepository->list($dto->toArray());

        return TimerCollectionViewModel::fromPaginator($paginator);
    }
}
