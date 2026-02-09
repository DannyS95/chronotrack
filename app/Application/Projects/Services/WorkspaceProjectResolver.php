<?php

namespace App\Application\Projects\Services;

use App\Infrastructure\Projects\Eloquent\Models\Project;

final class WorkspaceProjectResolver
{
    private const WORKSPACE_NAME = 'Daily Goals Workspace';
    private const WORKSPACE_DESCRIPTION = 'system:daily-goals-workspace';

    public function resolve(string $userId): Project
    {
        $workspace = Project::query()
            ->where('user_id', $userId)
            ->where('description', self::WORKSPACE_DESCRIPTION)
            ->first();

        if ($workspace !== null) {
            return $workspace;
        }

        return Project::query()->create([
            'name' => self::WORKSPACE_NAME,
            'description' => self::WORKSPACE_DESCRIPTION,
            'deadline' => null,
            'user_id' => $userId,
            'status' => 'active',
            'completed_at' => null,
            'completion_source' => null,
        ]);
    }
}
