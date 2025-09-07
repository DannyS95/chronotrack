<?php

namespace App\Infrastructure\Timers\Eloquent\Models;

use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;

class Timer extends BaseModel
{
    protected $table = 'timers';

    protected $fillable = [
        'user_id',
        'task_id',
        'started_at',
        'paused_at',
        'paused_total',
        'stopped_at',
        'duration',
    ];

    /**
     * Filters that can be applied through applyFilters()
     */
    public static function filterMap(): array
    {
        return [
            'id'            => 'equals',
            'task_id'       => 'equals',
            'started_after' => 'after.started_at',
            'started_before'=> 'before.started_at',
            'stopped_after' => 'after.stopped_at',
            'stopped_before'=> 'before.stopped_at',
            'active' => 'isnull.stopped_at',
        ];
    }
}
