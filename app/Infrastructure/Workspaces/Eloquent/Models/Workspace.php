<?php

namespace App\Infrastructure\Workspaces\Eloquent\Models;

use App\Infrastructure\Goals\Eloquent\Models\Goal;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use App\Infrastructure\Tasks\Eloquent\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Workspace extends BaseModel
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
        'status',
        'completed_at',
        'completion_source',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class, 'project_id');
    }
}
