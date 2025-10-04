<?php

namespace App\Infrastructure\Timers\Eloquent\Models;

use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timer extends BaseModel
{
    use SoftDeletes;
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

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'stopped_at' => 'datetime',
        'duration' => 'integer',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

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
