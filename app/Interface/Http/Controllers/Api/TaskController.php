<?php

namespace App\Interface\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Application\Tasks\Services\CreateTaskService;
use App\Application\Tasks\Services\ListTasksService;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Interface\Http\Requests\Tasks\StoreTaskRequest;
use App\Interface\Http\Requests\Tasks\TaskFilterRequest;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new CreateTaskDTO(...[
            ...$request->validated(),
            'project_id' => $project->id,
            'user_id' => $auth->id(),
        ]);

        $task = app(CreateTaskService::class)->handle($dto);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task,
        ], 201);
    }

    public function index(TaskFilterRequest $request, string $project_id): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new TaskFilterDTO(...[
            ...$request->validated(),
            'project_id' => $project_id,
            'user_id' => $auth->id(),
        ]);

        $tasks = app(ListTasksService::class)->handle($dto);

        return response()->json($tasks);
    }
}
