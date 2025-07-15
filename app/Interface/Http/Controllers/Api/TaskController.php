<?php

namespace App\Interface\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Application\Tasks\Services\CreateTaskService;
use App\Application\Tasks\Services\ListTasksService;
use App\Interface\Http\Requests\Tasks\StoreTaskRequest;
use App\Interface\Http\Requests\Tasks\TaskFilterRequest;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, string $projectId): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new CreateTaskDTO(...[
            ...$request->validated(),
            'projectId' => $projectId,
            'userId' => $auth->id(),
        ]);

        $task = app(CreateTaskService::class)->handle($dto);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task,
        ], 201);
    }

    public function index(TaskFilterRequest $request, string $projectId): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new TaskFilterDTO(...[
            ...$request->validated(),
            'projectId' => $projectId,
            'userId' => $auth->id(),
        ]);

        $tasks = app(ListTasksService::class)->handle($dto);

        return response()->json($tasks);
    }
}
