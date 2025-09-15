<?php

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Application\Goals\DTOs\CreateGoalDTO;
use App\Application\Goals\DTOs\GoalFilterDTO;
use App\Application\Goals\Services\CreateGoalService;
use App\Application\Goals\Services\ListGoalsService;
use App\Interface\Http\Controllers\Controller;
use App\Interface\Http\Requests\Goals\GoalFilterRequest;
use App\Interface\Http\Requests\Goals\StoreGoalRequest;
use Illuminate\Http\JsonResponse;

final class GoalController extends Controller
{
    public function __construct(
        private readonly ListGoalsService $listGoalsService,
        private readonly CreateGoalService $createGoalService,
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
            'project_id' => $project->id,
            'user_id'    => $auth->id(),
        ]);

        $goals = $this->listGoalsService->handle($dto);

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
}
