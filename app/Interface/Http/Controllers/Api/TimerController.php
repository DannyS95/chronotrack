<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Timers\DTO\TimerFilterDTO;
use App\Application\Timers\Services\ListTimersService;
use App\Application\Timers\Services\TimerService;
use App\Application\Timers\ViewModels\TimerViewModel;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Timers\TimerFilterRequest;
use Illuminate\Http\Request;

final class TimerController extends Controller
{
    public function __construct(private readonly TimerService $service, private readonly ListTimersService $listTimersService) {}

    public function index(TimerFilterRequest $request, Task $task)
    {
        $this->authorize('viewAny', [Timer::class, $task]);

        $dto = TimerFilterDTO::fromArray([
            ...$request->validated(),
            'task_id' => $task->id,
            'userId' => $request->user()->id,
        ]);

        $timers = $this->listTimersService->handle($dto);

        return response()->json($timers);
    }

    public function start(Request $request, Task $task)
    {
        $this->authorize('start', [Timer::class, $task]);

        $timer = $this->service->start($task, (string) $request->user()->id);

        return response()->json(
            TimerViewModel::fromModel($timer)->toArray(),
            201
        );
    }

    public function pause(Request $request, Task $task)
    {
        $this->authorize('pause', [Timer::class, $task]);

        $timer = $this->service->pause($task, (string) $request->user()->id);

        return response()->json(
            TimerViewModel::fromModel($timer)->toArray()
        );
    }

    public function stop(Request $request, Task $task)
    {
        $this->authorize('stop', [Timer::class, $task]);

        $timer = $this->service->stop($task, (string) $request->user()->id);

        return response()->json(
            TimerViewModel::fromModel($timer)->toArray()
        );
    }

    public function active(Request $request)
    {
        $timer = $this->service->activeForUser((string) $request->user()->id);

        return response()->json([
            'activeTimer' => $timer ? TimerViewModel::fromModel($timer)->toArray() : null,
        ]);
    }
}
