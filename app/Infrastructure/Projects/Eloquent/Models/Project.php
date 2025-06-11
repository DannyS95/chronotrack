<?php

namespace App\Infrastructure\Projects\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'deadline',
        // Add any other columns you plan to fill via create() or update()
    ];

    protected $keyType = 'string';
    public $incrementing = false;


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
            $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
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
}
