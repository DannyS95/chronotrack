<?php

namespace App\Infrastructure\Timers\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Timer extends Model
{
    use SoftDeletes, HasUuids;

    protected $table = 'timers';

    protected $keyType = 'string'; // important for uuid primary keys
    public $incrementing = false;  // disable auto-increment

    protected $fillable = [
        'id',
        'task_id',
        'started_at',
        'stopped_at',
        'duration',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(
            \App\Infrastructure\Tasks\Eloquent\Models\Task::class,
            'task_id'
        );
    }
}
