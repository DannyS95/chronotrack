<?php

namespace App\Infrastructure\Goals\Eloquent\Models;

use App\Infrastructure\Shared\Persistence\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public static function filterMap(): array
    {
        return [
            'title'               => 'like',
            'description'         => 'like',
            'status'              => 'equals',
            'from'                => 'after.created_at',
            'to'                  => 'before.created_at',
            'last_activity_from'  => 'after.last_activity_at',
            'last_activity_to'    => 'before.last_activity_at',
        ];
    }
}
