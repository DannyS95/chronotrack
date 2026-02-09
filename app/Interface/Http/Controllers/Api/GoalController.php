<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Goals\DTO\CompleteGoalDTO;
use App\Application\Goals\DTO\CreateGoalDTO;
use App\Application\Goals\DTO\GoalFilterDTO;
use App\Application\Goals\Services\CompleteGoalService;
use App\Application\Goals\Services\CreateGoalService;
use App\Application\Goals\Services\GoalProgressService;
use App\Application\Goals\Services\ListGoalsService;
use App\Application\Workspaces\Services\WorkspaceResolver;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Goals\GoalFilterRequest;
use App\Interface\Http\Requests\Goals\StoreGoalRequest;
use App\Interface\Http\Support\GoalResponseMapper;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class GoalController extends Controller
{
    public function __construct(
        private readonly ListGoalsService $listGoalsService,
        private readonly CreateGoalService $createGoalService,
        private readonly GoalProgressService $goalProgressService,
        private readonly CompleteGoalService $completeGoalService,
        private readonly WorkspaceResolver $workspaceResolver,
        private readonly GoalResponseMapper $goalResponseMapper,
    ) {}

    public function index(GoalFilterRequest $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceResolver->resolve($userId);

        $this->authorize('viewAny', [Goal::class, $workspace]);

        $dto = GoalFilterDTO::fromArray($request->validated());
        $goals = $this->listGoalsService->handle($dto, $workspace->id, $userId)
            ->map(fn(Goal $goal) => $this->goalResponseMapper->toGoalResponse($goal))
            ->values()
            ->all();

        return response()->json([
            'data' => $goals,
        ]);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceResolver->resolve($userId);

        $this->authorize('create', [Goal::class, $workspace]);

        $validated = $request->validated();
        $summary = $validated['summary'] ?? $validated['title'];
        $goalDate = $validated['goal_date'] ?? Carbon::parse($validated['deadline'])->format('Y-m-d');
        $deadline = Carbon::parse($goalDate)->endOfDay()->toDateTimeString();

        $dto = CreateGoalDTO::fromArray([
            'title' => $summary,
            'description' => $validated['description'] ?? null,
            'deadline' => $deadline,
            'status' => $validated['status'] ?? 'active',
            'project_id' => $workspace->id,
            'user_id' => $userId,
        ]);

        /** @var Goal $goal */
        $goal = $this->createGoalService->handle($dto);

        return response()->json([
            'message' => 'Daily goal created successfully.',
            'data' => $this->goalResponseMapper->toGoalResponse($goal),
        ], 201);
    }

    public function show(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('view', $goal);

        return response()->json([
            'data' => $this->goalResponseMapper->toGoalResponse($goal),
        ]);
    }

    public function progress(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('progress', $goal);

        $viewModel = $this->goalProgressService->handle(
            $workspace->id,
            $goal->id,
            $userId,
        );

        return response()->json([
            'goal' => $this->goalResponseMapper->toGoalResponse($goal),
            'progress' => $viewModel->toArray(),
        ]);
    }

    public function complete(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('complete', $goal);

        $dto = new CompleteGoalDTO(
            projectId: $workspace->id,
            goalId: $goal->id,
            userId: $userId,
        );

        $result = $this->completeGoalService->handle($dto);
        $freshGoal = $goal->fresh() ?? $goal;

        return response()->json([
            'message' => 'Daily goal marked complete.',
            'data' => [
                'goal' => $this->goalResponseMapper->toGoalResponse($freshGoal),
                'completion' => $result,
            ],
        ]);
    }

    private function assertGoalBelongsToWorkspace(Goal $goal, string $workspaceProjectId): void
    {
        abort_unless((string) $goal->project_id === (string) $workspaceProjectId, 404);
    }
}
