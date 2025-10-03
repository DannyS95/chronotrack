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
            ->ownedByUser($userId);
    }

    /**
     * Limit the query to records whose parent project belongs to the given user.
     */
    public function scopeOwnedByUser(Builder $query, string $userId): Builder
    {
        return $query->whereHas('project', function (Builder $builder) use ($userId) {
            return $builder->where('user_id', $userId);
        });
    }
}
