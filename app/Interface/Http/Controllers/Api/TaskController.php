<?php

namespace App\Interface\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\DeleteTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\Services\CreateTaskService;
use App\Application\Tasks\Services\DeleteTaskService;
use App\Application\Tasks\Services\ListTasksService;
use App\Application\Tasks\Services\ShowTaskService;
use App\Application\Tasks\Services\UpdateTaskService;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Interface\Http\Requests\Tasks\StoreTaskRequest;
use App\Interface\Http\Requests\Tasks\TaskFilterRequest;
use App\Interface\Http\Requests\Tasks\UpdateTaskRequest;

class TaskController extends Controller
{
    public function __construct(
        private readonly CreateTaskService $createTaskService,
        private readonly ListTasksService $listTasksService,
        private readonly ShowTaskService $showTaskService,
        private readonly UpdateTaskService $updateTaskService,
        private readonly DeleteTaskService $deleteTaskService,
    ) {}

    public function show(Project $project, Task $task): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $viewModel = $this->showTaskService->handle($project, $task, (string) $auth->id());

        return response()->json($viewModel->toArray());
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = CreateTaskDTO::fromArray([
            ...$request->validated(),
            'project_id' => $project->id,
            'user_id' => (string) $auth->id(),
        ]);

        $task = $this->createTaskService->handle($dto);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task->toArray(),
        ], 201);
    }

    public function index(TaskFilterRequest $request, Project $project): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = TaskFilterDTO::fromArray([
            ...$request->validated(),
            'project_id' => $project->id,
            'user_id'    => (string) $auth->id(),
        ]);

        $tasks = $this->listTasksService->handle($dto);

        return response()->json($tasks->toArray());
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new UpdateTaskDTO(
            project_id: $project->id,
            task_id: $task->id,
            user_id: (string) $auth->id(),
            attributes: $request->validated(),
        );

        $updatedTask = $this->updateTaskService->handle($dto);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $updatedTask->toArray(),
        ]);
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new DeleteTaskDTO(
            project_id: $project->id,
            task_id: $task->id,
            user_id: (string) $auth->id(),
        );

        $this->deleteTaskService->handle($dto);

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}
