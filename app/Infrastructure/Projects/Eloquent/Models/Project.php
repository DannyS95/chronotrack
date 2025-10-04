<?php

namespace App\Infrastructure\Projects\Eloquent\Models;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\User;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Project extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

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

    public function getRouteKeyName(): string
    {
        return 'id'; // so it binds UUID instead of default int
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public static function filters(): array
    {
        return [
            'name'          => 'like',
            'id'            => 'equals',
            'user_id'        => 'equals',
            'description'   => 'like',
            'deadline'      => 'date',
            'from'          => 'after.created_at',
            'to'            => 'before.created_at',
        ];
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'project_id');
    }
}
