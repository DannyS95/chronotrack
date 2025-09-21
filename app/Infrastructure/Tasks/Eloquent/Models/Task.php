<?php

namespace App\Infrastructure\Tasks\Eloquent\Models;

use App\Infrastructure\Projects\Eloquent\Models\Project;
use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Task extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'id',
        'project_id',
        'title',
        'description',
        'due_at',
        'last_activity_at',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function project()
    {
        return $this->belongsTo(
            Project::class,
            'project_id'
        );
    }

    public static function filters(): array
    {
        return [
            'id'              => 'equals',
            'project_id'      => 'equals',
            'title'           => 'like',
            'description'     => 'like',
            'priority'        => 'equals',

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
