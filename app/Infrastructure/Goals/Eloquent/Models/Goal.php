<?php

namespace App\Infrastructure\Goals\Eloquent\Models;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Goal extends BaseModel
{
    use HasFactory;

    protected $table = 'goals';

    protected $fillable = [
        'id',
        'title',
        'description',
        'target_date',
        'deadline',
        'project_id',
        'last_activity_at',
        'reminder_every_n_days',
        'completed_at',
        'status',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'goal_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }


    public static function filters(): array
    {
        return [
            'title'               => 'like',
            'id'                  => 'equals',
            'deadline'            => 'before',
            'description'         => 'like',
            'status'              => 'equals',
            'from'                => 'after',
            'to'                  => 'before',
            'last_activity_from'  => 'after',
            'last_activity_to'    => 'before',
        ];
    }
}
