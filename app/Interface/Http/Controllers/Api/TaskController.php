<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Projects\Services\WorkspaceProjectResolver;
use App\Application\Tasks\DTO\CreateTaskDTO;
use App\Application\Tasks\DTO\DeleteTaskDTO;
use App\Application\Tasks\DTO\TaskFilterDTO;
use App\Application\Tasks\DTO\UpdateTaskDTO;
use App\Application\Tasks\Services\CreateTaskService;
use App\Application\Tasks\Services\DeleteTaskService;
use App\Application\Tasks\Services\ListTasksService;
use App\Application\Tasks\Services\ShowTaskService;
use App\Application\Tasks\Services\UpdateTaskService;
use App\Domain\Tasks\Support\TaskTimerProfile;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Tasks\StoreTaskRequest;
use App\Interface\Http\Requests\Tasks\TaskFilterRequest;
use App\Interface\Http\Requests\Tasks\UpdateTaskRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class TaskController extends Controller
{
    public function __construct(
        private readonly CreateTaskService $createTaskService,
        private readonly ListTasksService $listTasksService,
        private readonly ShowTaskService $showTaskService,
        private readonly UpdateTaskService $updateTaskService,
        private readonly DeleteTaskService $deleteTaskService,
        private readonly WorkspaceProjectResolver $workspaceProjectResolver,
    ) {}

    public function storeForGoal(StoreTaskRequest $request, Goal $goal): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('view', $goal);

        $validated = $request->validated();
        $timerProfile = $this->normalizeTimerProfile(
            $validated['timer_type'],
            $validated['target_minutes'] ?? null,
        );

        $dto = CreateTaskDTO::fromArray([
            'project_id' => $workspace->id,
            'goal_id' => $goal->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'timer_type' => $timerProfile['timer_type'],
            'target_duration_seconds' => $timerProfile['target_duration_seconds'],
            'user_id' => $userId,
        ]);

        $task = $this->createTaskService->handle($dto);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task->toArray(),
        ], 201);
    }

    public function indexForGoal(TaskFilterRequest $request, Goal $goal): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('view', $goal);

        $dto = TaskFilterDTO::fromArray([
            ...$request->validated(),
            'project_id' => $workspace->id,
            'goal_id' => $goal->id,
            'user_id' => $userId,
        ]);

        $tasks = $this->listTasksService->handle($dto);

        return response()->json($tasks->toArray());
    }

    public function show(Task $task): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertTaskBelongsToWorkspace($task, $workspace->id);
        $this->authorize('view', $task);

        $viewModel = $this->showTaskService->handle($workspace, $task, $userId);

        return response()->json($viewModel->toArray());
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertTaskBelongsToWorkspace($task, $workspace->id);
        $this->authorize('update', $task);

        $attributes = $request->validated();

        if (array_key_exists('timer_type', $attributes)) {
            $timerProfile = $this->normalizeTimerProfile(
                $attributes['timer_type'],
                $attributes['target_minutes'] ?? null,
            );

            $attributes['timer_type'] = $timerProfile['timer_type'];
            $attributes['target_duration_seconds'] = $timerProfile['target_duration_seconds'];
        }

        unset($attributes['target_minutes']);

        $dto = new UpdateTaskDTO(
            project_id: $workspace->id,
            task_id: $task->id,
            user_id: $userId,
            attributes: $attributes,
        );

        $updatedTask = $this->updateTaskService->handle($dto);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $updatedTask->toArray(),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertTaskBelongsToWorkspace($task, $workspace->id);
        $this->authorize('delete', $task);

        $dto = new DeleteTaskDTO(
            project_id: $workspace->id,
            task_id: $task->id,
            user_id: $userId,
        );

        $this->deleteTaskService->handle($dto);

        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }

    private function assertGoalBelongsToWorkspace(Goal $goal, string $workspaceProjectId): void
    {
        abort_unless((string) $goal->project_id === (string) $workspaceProjectId, 404);
    }

    private function assertTaskBelongsToWorkspace(Task $task, string $workspaceProjectId): void
    {
        abort_unless((string) $task->project_id === (string) $workspaceProjectId, 404);
    }

    /**
     * @return array{timer_type:string,target_duration_seconds:int}
     */
    private function normalizeTimerProfile(string $timerType, mixed $targetMinutes): array
    {
        try {
            return TaskTimerProfile::normalize($timerType, $targetMinutes);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'timer_type' => [$exception->getMessage()],
            ]);
        }
    }
}
