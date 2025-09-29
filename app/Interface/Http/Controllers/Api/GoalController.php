<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use App\Application\Goals\DTO\CreateGoalDTO;
use App\Application\Goals\DTO\GoalFilterDTO;
use App\Application\Goals\Services\AttachTaskToGoalService;
use App\Application\Goals\Services\CreateGoalService;
use App\Application\Goals\Services\DetachTaskFromGoalService;
use App\Application\Goals\Services\ListGoalsService;
use App\Application\Goals\Services\ShowGoalService;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Goals\GoalFilterRequest;
use App\Interface\Http\Requests\Goals\StoreGoalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class GoalController extends Controller
{
    public function __construct(
        private readonly ListGoalsService $listGoalsService,
        private readonly CreateGoalService $createGoalService,
        private AttachTaskToGoalService $attachService,
        private DetachTaskFromGoalService $detachService,
        private readonly ShowGoalService $showGoalService,
    ) {}

    /**
     * List all goals for a project.
     */
    public function index(GoalFilterRequest $request, Project $project): JsonResponse
    {
        $dto = new GoalFilterDTO(...[
            ...$request->validated(),
        ]);

        $goals = $this->listGoalsService->handle($dto, $project);

        return response()->json($goals);
    }

    /**
     * Create a new goal in a project.
     */
    public function store(StoreGoalRequest $request, Project $project)
    {
        $dto = new CreateGoalDTO(...[
            ...$request->validated(),
            'project_id' => $project->id,
            'user_id'    => $request->user()->id,
        ]);

        $goal = $this->createGoalService->handle($dto);

        return response()->json($goal, 201);
    }

    public function show(Project $project, Goal $goal): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $goalData = $this->showGoalService->handle(
            $project,
            $goal,
            $auth->id(),
        );

        return response()->json($goalData);
    }

    public function attach(Request $request, Project $project, Goal $goal, Task $task): JsonResponse
    {
        $dto = new AttachTaskToGoalDTO(
            projectId: $project->id,
            goalId: $goal->id,
            taskId: $task->id,
            userId: $request->user()->id,
        );

        $this->attachService->handle($dto);

        return response()->json(['message' => 'Task attached to goal.']);
    }

    public function detach(Request $request, Project $project, Goal $goal, Task $task): JsonResponse
    {
        $dto = new AttachTaskToGoalDTO(
            projectId: $project->id,
            goalId: $goal->id,
            taskId: $task->id,
            userId: $request->user()->id,
        );

        $this->detachService->handle($dto);

        return response()->json(['message' => 'Task detached from goal.']);
    }
}
