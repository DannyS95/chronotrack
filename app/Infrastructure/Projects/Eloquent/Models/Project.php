<?php

namespace App\Infrastructure\Projects\Eloquent\Models;

use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

final class Project extends BaseModel
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'id',
        'name',
        'description',
        'deadline',
        'user_id',
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

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'project_user',
            'project_id',
            'user_id'
        );
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public static function filterMap(): array
    {
        return [
            'name'          => 'like',
            'description'   => 'like',
            'deadlineFrom'  => 'after.deadline',
            'deadlineTo'    => 'before.deadline',
            'from'          => 'after.created_at',
            'to'            => 'before.created_at',
            'project_id'    => 'equals',
        ];
    }
}
