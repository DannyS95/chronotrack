<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Projects\DTO\CreateProjectDTO;
use App\Application\Projects\DTO\ProjectFilterDTO;
use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Projects\Services\CreateProjectService;
use App\Application\Projects\Services\ListProjectsService;
use App\Interface\Http\Requests\Projects\ProjectFilterRequest;
use App\Interface\Http\Requests\Projects\StoreProjectRequest;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

         $dto = new CreateProjectDTO(...[
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
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        
        $dto = new ProjectFilterDTO(...[
            ...$request->validated(),
            'user_id' => $auth->id(),
        ]);

        $projects = app(ListProjectsService::class)->handle($dto);

        return response()->json($projects);
    }
}
