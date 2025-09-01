<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Timers\Services\TimerService;
use App\Interface\Http\Controllers\Controller;
use Illuminate\Http\Request;

final class TimerController extends Controller
{
    public function __construct(private readonly TimerService $service) {}

    public function start(Request $request, string $task)
    {
        $dto = $this->service->start($task, (int) $request->user()->id);
        return response()->json($dto->toArray(), 201);
    }

    public function stop(Request $request, string $task)
    {
        $dto = $this->service->stop($task, (int) $request->user()->id);
        return response()->json($dto->toArray());
    }

    public function active(Request $request)
    {
        $payload = $this->service->activeForUser((int) $request->user()->id);
        return response()->json(['activeTimer' => $payload]);
    }
}
