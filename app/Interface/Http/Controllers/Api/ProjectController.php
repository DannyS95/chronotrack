<?php

namespace App\Interface\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Interface\Http\Controllers\Controller;
use App\Application\Projects\Services\CreateProjectService;
use App\Interface\Http\Requests\Projects\StoreProjectRequest;

final class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = app(CreateProjectService::class)->handle($request->all());

        return response()->json($project, 201);
    }
}
