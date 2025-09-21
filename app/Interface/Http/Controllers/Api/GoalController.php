<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Goals\DTO\AttachTaskToGoalDTO;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Application\Goals\DTO\CreateGoalDTO;
use App\Application\Goals\DTO\GoalFilterDTO;
use App\Application\Goals\Services\AttachTaskToGoalService;
use App\Application\Goals\Services\CreateGoalService;
use App\Application\Goals\Services\DetachTaskFromGoalService;
use App\Application\Goals\Services\ListGoalsService;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Goals\GoalFilterRequest;
use App\Interface\Http\Requests\Goals\StoreGoalRequest;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;

final class GoalController extends Controller
{
    public function __construct(
        private readonly ListGoalsService $listGoalsService,
        private readonly CreateGoalService $createGoalService,
        private AttachTaskToGoalService $attachService,
        private DetachTaskFromGoalService $detachService,
    ) {}

    /**
     * List all goals for a project.
     */
    public function index(GoalFilterRequest $request, Project $project): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

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

    public function attach(Request $request, string $projectId, string $goalId, string $taskId): JsonResponse
    {
        $dto = new AttachTaskToGoalDTO(
            projectId: $projectId,
            goalId: $goalId,
            taskId: $taskId,
            userId: $request->user()->id,
        );

        $this->attachService->handle($dto);

        return response()->json(['message' => 'Task attached to goal.']);
    }

    public function detach(Request $request, string $projectId, string $goalId, string $taskId): JsonResponse
    {
        $dto = new AttachTaskToGoalDTO(
            projectId: $projectId,
            goalId: $goalId,
            taskId: $taskId,
            userId: $request->user()->id,
        );

        $this->detachService->handle($dto);

        return response()->json(['message' => 'Task detached from goal.']);
    }
}
