<?php

namespace App\Infrastructure\Shared\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Use UUIDs as primary keys.
     */
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public static function applyFilters(array $filters): Builder
    {
        $query = static::query();
        $map = static::filterMap();

        // Apply sorting first
        if (isset($filters['sort_by'])) {
            $query->orderBy($filters['sort_by'], $filters['order'] ?? 'asc');
        }

        foreach ($map as $param => $rule) {
            if (!array_key_exists($param, $filters) || is_null($filters[$param])) {
                continue;
            }

            $value = $filters[$param];

            if ($rule === 'equals') {
                $query->where($param, '=', $value);
                continue;
            }

            if ($rule === 'like') {
                $query->where($param, 'LIKE', '%' . $value . '%');
                continue;
            }

            if (str_contains($rule, '.')) {
                [$operator, $column] = explode('.', $rule);

                if ($operator === 'isnull') {
                    if ($value) {
                        $query->whereNull($column);
                    } else {
                        $query->whereNotNull($column);
                    }
                    continue;
                }

                $query->where($column, static::resolveOperator($operator), $value);
                continue;
            }


            throw new \InvalidArgumentException("Unknown filter rule: {$rule}");
        }

        return $query;
    }

    private static function resolveOperator(string $operator): string
    {
        return match ($operator) {
            'equals' => '=',
            'after' => '>=',
            'before' => '<=',
            default  => throw new \InvalidArgumentException("Unsupported operator: {$operator}"),
        };
    }
}
