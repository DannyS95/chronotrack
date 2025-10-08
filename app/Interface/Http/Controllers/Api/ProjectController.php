<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Projects\DTO\ArchiveProjectDTO;
use App\Application\Projects\DTO\CompleteProjectDTO;
use App\Application\Projects\DTO\CreateProjectDTO;
use App\Application\Projects\DTO\ProjectFilterDTO;
use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Projects\Services\ArchiveProjectService;
use App\Application\Projects\Services\CompleteProjectService;
use App\Application\Projects\Services\CreateProjectService;
use App\Application\Projects\Services\ListProjectsService;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Interface\Http\Requests\Projects\ProjectFilterRequest;
use App\Interface\Http\Requests\Projects\StoreProjectRequest;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = CreateProjectDTO::fromArray([
            ...$request->validated(),
            'user_id' => $auth->id(),
        ]);

        $project = app(CreateProjectService::class)->handle($dto);

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function index(ProjectFilterRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = ProjectFilterDTO::fromArray([
            ...$request->validated(),
            'user_id' => $auth->id(),
        ]);

        $projects = app(ListProjectsService::class)->handle($dto);

        return response()->json($projects);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new ArchiveProjectDTO(
            projectId: $project->id,
            userId: (string) $auth->id(),
        );

        $result = app(ArchiveProjectService::class)->handle($dto);

        return response()->json([
            'message' => 'Project archived successfully.',
            'data' => $result,
        ]);
    }

    public function complete(Project $project): JsonResponse
    {
        $this->authorize('complete', $project);

        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $dto = new CompleteProjectDTO(
            projectId: $project->id,
            userId: (string) $auth->id(),
        );

        $result = app(CompleteProjectService::class)->handle($dto);

        return response()->json([
            'message' => 'Project marked as complete.',
            'data' => $result,
        ]);
    }
}
