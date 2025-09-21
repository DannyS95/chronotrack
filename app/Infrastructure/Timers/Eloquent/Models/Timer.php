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
     * Filters that can be applied through filters()
     */
    public static function filters(): array
    {
        return [
            'id'            => 'equals',
            'task_id'       => 'equals',
            'started_at'    => 'date',
            'stopped_at'    => 'date',
            'active'        => 'isnull.stopped_at',
        ];
    }
}
