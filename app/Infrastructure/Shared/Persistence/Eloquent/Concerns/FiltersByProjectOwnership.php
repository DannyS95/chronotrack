<?php

namespace App\Infrastructure\Shared\Persistence\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByProjectOwnership
{
    /**
     * Limit the query to records for a project owned by the given user.
     */
    public function scopeOwnedBy(Builder $query, string $projectId, string $userId): Builder
    {
        return $query
            ->where('project_id', $projectId)
            ->whereHas('project', fn(Builder $projectQuery) => $projectQuery->where('user_id', $userId));
    }
}
