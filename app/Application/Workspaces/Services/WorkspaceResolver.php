<?php

namespace App\Application\Workspaces\Services;

use App\Infrastructure\Workspaces\Eloquent\Models\Workspace;

final class WorkspaceResolver
{
    private const DEFAULT_WORKSPACE_NAME = 'Daily Goals Workspace';
    private const DEFAULT_WORKSPACE_MARKER = 'system:daily-goals-workspace';

    public function resolve(string $userId): Workspace
    {
        $workspace = Workspace::query()
            ->where('user_id', $userId)
            ->where('description', self::DEFAULT_WORKSPACE_MARKER)
            ->first();

        if ($workspace !== null) {
            return $workspace;
        }

        return Workspace::query()->create([
            'name' => self::DEFAULT_WORKSPACE_NAME,
            'description' => self::DEFAULT_WORKSPACE_MARKER,
            'deadline' => null,
            'user_id' => $userId,
            'status' => 'active',
            'completed_at' => null,
            'completion_source' => null,
        ]);
    }
}
