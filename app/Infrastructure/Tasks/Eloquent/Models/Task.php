<?php

namespace App\Infrastructure\Tasks\Eloquent\Models;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Concerns\FiltersByProjectOwnership;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Timers\Eloquent\Models\Timer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Task extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use FiltersByProjectOwnership;

    protected $table = 'tasks';

    protected $fillable = [
        'id',
        'project_id',
        'goal_id',
        'title',
        'description',
        'due_at',
        'last_activity_at',
        'status',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function project(): BelongsTo
    {
        return $this->belongsTo(
            Project::class,
            'project_id'
        );
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(
            Goal::class,
            'goal_id'
        );
    }

    public function timers(): HasMany
    {
        return $this->hasMany(Timer::class, 'task_id');
    }

    public static function filters(): array
    {
        return [
            'id'              => 'equals',
            'project_id'      => 'equals',
            'goal_id'         => 'equals',
            'title'           => 'like',
            'description'     => 'like',
            'priority'        => 'equals',
            'status'          => 'equals',

            // Due date filters
            'due_from'        => 'after.due_at',
            'due_to'          => 'before.due_at',

            // Last activity filters
            'last_activity_from' => 'after.last_activity_at',
            'last_activity_to'   => 'before.last_activity_at',

            // Creation date filters
            'from'            => 'after.created_at',
            'to'              => 'before.created_at',
        ];
    }

}
