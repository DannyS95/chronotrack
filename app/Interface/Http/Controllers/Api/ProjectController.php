<?php

namespace App\Interface\Http\Controllers\Api;

use App\Application\Projects\DTO\CreateProjectDTO;
use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Projects\Services\CreateProjectService;
use App\Application\Projects\Services\ListProjectsService;
use App\Interface\Http\Requests\Projects\StoreProjectRequest;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

         $dto = new CreateProjectDTO(...[
            ...$request->validated(),
            'userId' => $auth->id(),
        ]);

        $project = app(CreateProjectService::class)->handle($dto);

        return response()->json([
            'message' => 'Project created successfully.',
            'data' => $project,
        ], 201);
    }

    public function index(): JsonResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();

        $projects = app(ListProjectsService::class)->handle($auth->id());

        return response()->json($projects);
    }
}
