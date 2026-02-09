<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Goals\DTO\CompleteGoalDTO;
use App\Application\Goals\DTO\CreateGoalDTO;
use App\Application\Goals\DTO\GoalFilterDTO;
use App\Application\Goals\Services\CompleteGoalService;
use App\Application\Goals\Services\CreateGoalService;
use App\Application\Goals\Services\GoalProgressService;
use App\Application\Goals\Services\ListGoalsService;
use App\Application\Projects\Services\WorkspaceProjectResolver;
use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Goals\GoalFilterRequest;
use App\Interface\Http\Requests\Goals\StoreGoalRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class GoalController extends Controller
{
    public function __construct(
        private readonly ListGoalsService $listGoalsService,
        private readonly CreateGoalService $createGoalService,
        private readonly GoalProgressService $goalProgressService,
        private readonly CompleteGoalService $completeGoalService,
        private readonly WorkspaceProjectResolver $workspaceProjectResolver,
    ) {}

    public function index(GoalFilterRequest $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->authorize('viewAny', [Goal::class, $workspace]);

        $dto = GoalFilterDTO::fromArray($request->validated());
        $goals = $this->listGoalsService->handle($dto, $workspace)
            ->map(fn(Goal $goal) => $this->presentGoal($goal))
            ->values()
            ->all();

        return response()->json([
            'data' => $goals,
        ]);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $userId = (string) $request->user()->id;
        $workspace = $this->workspaceProjectResolver->resolve($userId);

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
            'data' => $this->presentGoal($goal),
        ], 201);
    }

    public function show(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('view', $goal);

        return response()->json([
            'data' => $this->presentGoal($goal),
        ]);
    }

    public function progress(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

        $this->assertGoalBelongsToWorkspace($goal, $workspace->id);
        $this->authorize('progress', $goal);

        $viewModel = $this->goalProgressService->handle(
            $workspace->id,
            $goal->id,
            $userId,
        );

        return response()->json([
            'goal' => $this->presentGoal($goal),
            'progress' => $viewModel->toArray(),
        ]);
    }

    public function complete(Goal $goal): JsonResponse
    {
        $userId = (string) auth()->id();
        $workspace = $this->workspaceProjectResolver->resolve($userId);

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
                'goal' => $this->presentGoal($freshGoal),
                'completion' => $result,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentGoal(Goal $goal): array
    {
        $goalDate = $goal->deadline instanceof \DateTimeInterface
            ? $goal->deadline->format('Y-m-d')
            : ($goal->deadline ? Carbon::parse($goal->deadline)->format('Y-m-d') : null);

        $completedAt = match (true) {
            $goal->completed_at instanceof \DateTimeInterface => $goal->completed_at->format('c'),
            is_string($goal->completed_at) => $goal->completed_at,
            default => null,
        };

        return [
            'id' => $goal->id,
            'summary' => $goal->title,
            'description' => $goal->description,
            'goal_date' => $goalDate,
            'status' => $goal->status,
            'completed_at' => $completedAt,
            'created_at' => $goal->created_at?->toISOString(),
            'updated_at' => $goal->updated_at?->toISOString(),
        ];
    }

    private function assertGoalBelongsToWorkspace(Goal $goal, string $workspaceProjectId): void
    {
        abort_unless((string) $goal->project_id === (string) $workspaceProjectId, 404);
    }
}
